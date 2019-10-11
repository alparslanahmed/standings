<?php

namespace App\Jobs\flashscore;

use App\Country;
use App\League;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use Symfony\Component\DomCrawler\Crawler;

class GetCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $host = 'https://www.flashscore.com';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch();

        $page = $browser->newPage();
        $page->goto($this->host);

        $page->click('[id^=lmenu] a');

        $html = $page->evaluate(JsFunction::createWithBody('return document.documentElement.outerHTML'));

        $browser->close();

        $crawler = new Crawler($html);

        $countries = $crawler->filter('[id^=lmenu]');

        $countries->each(function ($country) {
            $country_name = $country->filter('a')->first()->text();

            $countryModel = Country::updateOrCreate(
                ['name' => $country_name],
                ['name' => $country_name]
            );

            $leagues = $country->filter('.submenu a');

            $leagues->each(function ($leagueLink) use ($countryModel) {
                $league_name = $leagueLink->text();

                $leagueUrl = $this->host . (string)$leagueLink->attr('href');

                $league = League::updateOrCreate(
                    ['name' => $league_name, 'country_id' => $countryModel->id],
                    ['name' => $league_name, 'country_id' => $countryModel->id]
                );

                GetTableJob::dispatch($league->id, $leagueUrl);
            });
        });

    }
}
