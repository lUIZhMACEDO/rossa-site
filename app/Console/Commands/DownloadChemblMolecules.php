<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class DownloadChemblMolecules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rossa:download-molecules {url : The URL of the ChEMBL zip file to download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download ChEMBL molecule CSV files from the ChEMBL website';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '128M');

        $url = $this->argument('url');
        $extractTo = base_path('database/data/chembl_molecules');
        $destination = $extractTo.'/chembl_molecules.zip';

        // Ensure the extraction directory exists
        if (! is_dir($extractTo)) {
            mkdir($extractTo, recursive: true);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 600); // 10 min
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60); // 1 min
        $f_ptr = fopen($destination, 'w');
        curl_setopt($curl, CURLOPT_FILE, $f_ptr);
        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        fclose($f_ptr);

        if ($result && $code == 200) {
            $this->info("Downloaded zip file to: {$destination}");

            $zip = new ZipArchive;
            if ($zip->open($destination) === true) {
                $zip->extractTo($extractTo);
                $zip->close();
                $this->info("Extracted zip contents to: {$extractTo}");
                File::delete($destination);
                $this->info("Deleted zip file: {$destination}");
            } else {
                $this->error('Could not extract zip file.');
            }
        } else {
            $this->error('Could not download zip file. HTTP Status: '.$code);
        }
    }
}
