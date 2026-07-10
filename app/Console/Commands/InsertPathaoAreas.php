<?php

namespace App\Console\Commands;

use App\Models\PathaoArea;
use App\Utility\Pathao\AreaAPIUtility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;

class InsertPathaoAreas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:pathaoarea';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            //code...
            // DB::beginTransaction();
            // DB::table('pathao_areas')->truncate();
            $areautility = new AreaAPIUtility;
            $cities = cache()->remember('pathao_cities', 8600, function () use ($areautility) {
                $citiess = json_decode(json_encode($areautility->city()), true);
                return $citiess['data'];
            });

            $finalResult = [];
            foreach ($cities as $city) {
                $cityId = $city["city_id"];
                $cityName = $city["city_name"];

                $zoness = json_decode(json_encode($areautility->zone($cityId)), true);

                sleep(3); // Add a delay of 3 seconds between API calls to avoid hitting rate limits

                $zones = $zoness['data'];
                foreach ($zones as $zone) {
                    $zoneId = $zone["zone_id"];
                    $zoneName = $zone["zone_name"];

                    $areas = json_decode(json_encode($areautility->area($zoneId)), true);

                    sleep(3); // Add a delay of 3 seconds between API calls to avoid hitting rate limits

                    $areas = $areas['data'];
                    foreach ($areas as $area) {
                        $areaId = $area["area_id"];
                        $areaName = $area["area_name"];

                        $data = [
                            "city_id" => $cityId,
                            "city_name" => $cityName,
                            "zone_id" => $zoneId,
                            "zone_name" => $zoneName,
                            "area_id" => $areaId,
                            "area_name" => $areaName,
                            "full_area_name" => $cityName . ' > ' . $zoneName . ' > ' . $areaName,
                            "home_delivery_available" => $area["home_delivery_available"],
                            "pickup_available" => $area["pickup_available"]
                        ];

                        // Check if area_id already exists in PathaoArea table
                        if (!PathaoArea::where('area_id', $areaId)->exists()) {
                            $insertArea = PathaoArea::create($data);
                        }

                        // $insertArea = PathaoArea::create($data);

                        $finalResult[] = [
                            "city_id" => $cityId,
                            "zone_id" => $zoneId,
                            "area_id" => $areaId,
                            "area_name" => $areaName,
                            "home_delivery_available" => $area["home_delivery_available"],
                            "pickup_available" => $area["pickup_available"]
                        ];
                    }
                }
            }

            // Log::info($finalResult);
            // DB::commit();

            $this->info('Pathao area insert command was successful!');
        } catch (\Throwable $th) {
            // throw $th;
            // DB::rollBack();
            $this->error($th->getMessage());
        }
    }
}
