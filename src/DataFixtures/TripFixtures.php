<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Enum\CatapultModel;
use App\Entity\Trip;
use App\Repository\CityRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TripFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Seed of the pseudo-random generator, pinned so every workstation loads the same data.
     */
    private const SEED = 20260828;

    /**
     * Number of days covered by the schedule, starting today.
     */
    private const WINDOW_DAYS = 21;

    /**
     * Number of trips persisted between two flushes.
     */
    private const BATCH_SIZE = 200;

    /**
     * The four daily departure times, each with the coefficient it applies to the fare.
     */
    private const DEPARTURE_SLOTS = [
        '07:15' => 1.25,
        '11:40' => 0.90,
        '16:05' => 1.15,
        '19:30' => 0.85,
    ];

    /**
     * Approximate coordinates of the ten cities of the network, in decimal degrees.
     */
    private const COORDINATES = [
        'Bordeaux' => [44.84, -0.58],
        'Brest' => [48.39, -4.49],
        'Dijon' => [47.32, 5.04],
        'Lille' => [50.63, 3.06],
        'Lyon' => [45.76, 4.83],
        'Marseille' => [43.30, 5.37],
        'Nantes' => [47.22, -1.55],
        'Paris' => [48.86, 2.35],
        'Strasbourg' => [48.57, 7.75],
        'Toulouse' => [43.60, 1.44],
    ];

    /**
     * Minutes spent arming the catapult and receiving the passengers, whatever the distance.
     * Part of the flight duration, and unrelated to the boarding call below.
     */
    private const GROUND_MINUTES = 12;

    /**
     * Minutes before departure passengers are asked to report at the launch pad.
     * Printed in the boarding notice, and counted in no duration.
     */
    private const BOARDING_CALL_MINUTES = 30;

    /**
     * Cruise speed of a loaded catapult shot, in kilometers per minute.
     */
    private const CRUISE_KM_PER_MINUTE = 23.0;

    /**
     * Largest deviation, in minutes, the launch angle adds to or removes from a flight.
     */
    private const ANGLE_JITTER_MINUTES = 4;

    /**
     * Fixed part of a fare, in cents.
     */
    private const PRICE_FLOOR_CENTS = 1400;

    /**
     * Variable part of a fare, in cents per minute of flight.
     */
    private const PRICE_PER_MINUTE_CENTS = 100;

    /**
     * Origin of the scenario pinned for classroom demonstrations.
     */
    private const SCENARIO_ORIGIN = 'Paris';

    /**
     * Destination of the scenario pinned for classroom demonstrations.
     */
    private const SCENARIO_DESTINATION = 'Marseille';

    /**
     * Number of days between today and the pinned scenario, unless the environment says otherwise.
     */
    private const SCENARIO_DAY_OFFSET = 14;

    /**
     * The four pinned trips, as departure time, duration in minutes, price in cents and model.
     */
    private const SCENARIO_TRIPS = [
        ['07:15', 42, 6800, CatapultModel::OnagreM3],
        ['11:40', 42, 5400, CatapultModel::OnagreM3],
        ['16:05', 38, 7900, CatapultModel::BalisteXR],
        ['19:30', 45, 4900, CatapultModel::Mangonneau700],
    ];

    // le repository n'est pas construit ici, il est demandé au conteneur
    public function __construct(
        private readonly CityRepository $cityRepository,
    ) {
    }

    /**
     * Declares that the ten cities must exist before a single trip is written.
     *
     * @return array<int, class-string>
     */
    public function getDependencies(): array
    {
        return [AppFixtures::class];
    }

    /**
     * Loads the whole schedule of the network, the pinned demonstration day set aside
     * during the loop and written last.
     */
    public function load(ObjectManager $manager): void
    {
        // la graine est fixée : le générateur ne tire pas au hasard, il tire toujours le même hasard
        mt_srand(self::SEED);

        // en dev, le profileur retient chaque requête SQL et la pile d'appels qui l'a produite :
        // 7 560 insertions débordent les 128 Mo par défaut, et un jeu de démonstration
        // n'a aucune raison d'échouer là-dessus
        ini_set('memory_limit', '512M');

        $now = new \DateTimeImmutable();
        $today = new \DateTimeImmutable('today');
        $scenarioDay = $this->scenarioDay($today);

        $cities = $this->readCities();
        $models = CatapultModel::cases();

        $written = 0;
        $coveredPairs = [];

        // on itère sur les noms : la carte des villes, elle, est relue à chaque lot vidé
        $names = array_keys($cities);

        foreach ($names as $originName) {
            foreach ($names as $destinationName) {
                // un couple est orienté : on ne dessert pas une ville depuis elle-même,
                // et Paris → Marseille n'est pas Marseille → Paris
                if ($originName === $destinationName) {
                    continue;
                }

                $baseDuration = $this->baseDuration($originName, $destinationName);

                for ($dayOffset = 0; $dayOffset < self::WINDOW_DAYS; ++$dayOffset) {
                    $day = $today->modify(sprintf('+%d days', $dayOffset));

                    // ce couple, ce jour-là, est écrit à la main après la boucle
                    if ($this->isScenario($originName, $destinationName, $day, $scenarioDay)) {
                        continue;
                    }

                    // les villes sont relues après chaque lot vidé : on les résout donc ici,
                    // et jamais avant une frontière de lot
                    $origin = $cities[$originName];
                    $destination = $cities[$destinationName];

                    foreach (self::DEPARTURE_SLOTS as $time => $coefficient) {
                        // l'angle de lancer fait varier la durée de quelques minutes autour de la base
                        $duration = $baseDuration + mt_rand(-self::ANGLE_JITTER_MINUTES, self::ANGLE_JITTER_MINUTES);
                        $model = $models[mt_rand(0, count($models) - 1)];

                        $manager->persist($this->buildTrip(
                            $origin,
                            $destination,
                            $this->departureAt($day, $time),
                            $duration,
                            $this->price($duration, $coefficient),
                            $model,
                            $now,
                        ));

                        ++$written;
                    }

                    // le couple ne compte comme couvert qu'une fois ses lancers écrits :
                    // une attestation qui compterait les couples entrepris n'attesterait rien
                    $coveredPairs[$originName.' → '.$destinationName] = true;

                    if (0 === $written % self::BATCH_SIZE) {
                        $manager->flush();

                        // clear() détache tout, y compris les dix villes lues plus haut : on les relit
                        $manager->clear();
                        $cities = $this->readCities();
                    }
                }
            }
        }

        $manager->flush();
        $manager->clear();

        // le scénario épinglé est écrit en dernier : sur ce couple et ce jour, il fait foi
        $cities = $this->readCities();

        foreach (self::SCENARIO_TRIPS as [$time, $duration, $price, $model]) {
            $manager->persist($this->buildTrip(
                $cities[self::SCENARIO_ORIGIN],
                $cities[self::SCENARIO_DESTINATION],
                $this->departureAt($scenarioDay, $time),
                $duration,
                $price,
                $model,
                $now,
            ));

            ++$written;
        }

        $coveredPairs[self::SCENARIO_ORIGIN.' → '.self::SCENARIO_DESTINATION] = true;

        $manager->flush();

        $this->certify(count($coveredPairs), count($cities), $written, $scenarioDay);
    }

    /**
     * Returns the ten cities of the network, indexed and ordered by name.
     *
     * @return array<string, City>
     */
    private function readCities(): array
    {
        $cities = [];

        foreach ($this->cityRepository->findBy([], ['name' => 'ASC']) as $city) {
            $cities[$city->getName()] = $city;
        }

        return $cities;
    }

    /**
     * Returns the day the pinned scenario runs on, overridable through TRIP_SCENARIO_DATE.
     *
     * @throws \InvalidArgumentException when that variable does not hold a YYYY-MM-DD date
     */
    private function scenarioDay(\DateTimeImmutable $today): \DateTimeImmutable
    {
        // la date de démonstration est pilotable depuis l'environnement, J+14 par défaut ;
        // la variable est déclarée, commentée, dans le .env du dépôt
        $date = $_ENV['TRIP_SCENARIO_DATE'] ?? $_SERVER['TRIP_SCENARIO_DATE'] ?? null;

        if (!is_string($date) || '' === $date) {
            return $today->modify(sprintf('+%d days', self::SCENARIO_DAY_OFFSET));
        }

        // le « ! » cale l'heure à minuit ; la relecture du format attrape ce que
        // createFromFormat() aurait rattrapé tout seul, un 31 février par exemple
        $scenarioDay = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        if (false === $scenarioDay || $scenarioDay->format('Y-m-d') !== $date) {
            // une date mal formée se dit ici, avec le nom de la variable, et non trois écrans
            // plus loin sous la forme d'un scénario introuvable
            throw new \InvalidArgumentException(sprintf(
                'TRIP_SCENARIO_DATE expects a YYYY-MM-DD date, got "%s".',
                $date,
            ));
        }

        return $scenarioDay;
    }

    /**
     * Tells whether a pair and a day are the ones the pinned scenario owns.
     */
    private function isScenario(string $origin, string $destination, \DateTimeImmutable $day, \DateTimeImmutable $scenarioDay): bool
    {
        return self::SCENARIO_ORIGIN === $origin
            && self::SCENARIO_DESTINATION === $destination
            && $day->format('Y-m-d') === $scenarioDay->format('Y-m-d');
    }

    /**
     * Returns the straight-line distance between two cities of the network, in kilometers.
     */
    private function distanceKm(string $origin, string $destination): float
    {
        [$originLatitude, $originLongitude] = self::COORDINATES[$origin];
        [$destinationLatitude, $destinationLongitude] = self::COORDINATES[$destination];

        // approximation plane, suffisante à l'échelle du pays : un degré de latitude vaut 111 km,
        // un degré de longitude vaut ces mêmes 111 km rétrécis par le cosinus de la latitude moyenne
        $latitudeKm = ($destinationLatitude - $originLatitude) * 111.0;
        $longitudeKm = ($destinationLongitude - $originLongitude) * 111.0
            * cos(deg2rad(($originLatitude + $destinationLatitude) / 2));

        return sqrt($latitudeKm ** 2 + $longitudeKm ** 2);
    }

    /**
     * Returns the reference flight duration of a pair of cities, in minutes.
     */
    private function baseDuration(string $origin, string $destination): int
    {
        // la durée est une propriété du couple, pas du lancer : deux départs du même couple
        // ne se distinguent ensuite que par l'angle, quelques minutes, jamais cinquante
        return (int) round(self::GROUND_MINUTES + $this->distanceKm($origin, $destination) / self::CRUISE_KM_PER_MINUTE);
    }

    /**
     * Returns the fare of a trip, in cents, from its duration and the coefficient of its slot.
     */
    private function price(int $duration, float $coefficient): int
    {
        $base = self::PRICE_FLOOR_CENTS + $duration * self::PRICE_PER_MINUTE_CENTS;

        // arrondi au demi-euro : un tarif ne s'affiche pas au centime près
        return (int) round($base * $coefficient / 50) * 50;
    }

    /**
     * Returns the departure instant of a trip, from its day and the time of its slot.
     */
    private function departureAt(\DateTimeImmutable $day, string $time): \DateTimeImmutable
    {
        [$hour, $minute] = explode(':', $time);

        return $day->setTime((int) $hour, (int) $minute);
    }

    /**
     * Returns the boarding notice of a trip, written for the launch pad it departs from.
     */
    private function boardingInfo(string $origin): string
    {
        // le texte ne répète ni la franchise de bagage ni le modèle : la réponse du détail porte
        // déjà les deux, l'une calculée par maxBaggageWeightKg(), l'autre lue dans sa colonne.
        // Les graver ici en ferait des copies, libres de contredire leur original.
        return sprintf(
            'Présentez-vous au pas de tir de %s %d minutes avant le lancer, muni d\'une pièce '
            .'d\'identité. L\'équipe de sol pèse les bagages, ajuste le harnais et contrôle le '
            .'contrepoids avant l\'armement. Le casque est fourni et reste à bord.',
            $origin,
            self::BOARDING_CALL_MINUTES,
        );
    }

    /**
     * Builds one trip, boarding notice and creation date included.
     */
    private function buildTrip(
        City $origin,
        City $destination,
        \DateTimeImmutable $departureAt,
        int $duration,
        int $price,
        CatapultModel $model,
        \DateTimeImmutable $now,
    ): Trip {
        $trip = new Trip();
        $trip->setOrigin($origin);
        $trip->setDestination($destination);
        $trip->setDepartureAt($departureAt);
        $trip->setDuration($duration);
        $trip->setPrice($price);
        $trip->setCatapultModel($model);
        $trip->setBoardingInfo($this->boardingInfo($origin->getName()));

        // createdAt n'est pas nullable sur AbstractEntity : l'oublier fait échouer le flush
        $trip->setCreatedAt($now);

        return $trip;
    }

    /**
     * Prints what the fixture actually wrote: the pairs carrying at least one persisted trip,
     * and the number of trips persisted, so the reader checks instead of trusting.
     */
    private function certify(int $coveredPairs, int $cityCount, int $written, \DateTimeImmutable $scenarioDay): void
    {
        $expectedPairs = $cityCount * ($cityCount - 1);

        echo sprintf(
            "%d/%d couples couverts · %d jours · %s lancers · scénario épinglé %s → %s au %s\n",
            $coveredPairs,
            $expectedPairs,
            self::WINDOW_DAYS,
            number_format($written, 0, ',', ' '),
            self::SCENARIO_ORIGIN,
            self::SCENARIO_DESTINATION,
            $scenarioDay->format('Y-m-d'),
        );
    }
}
