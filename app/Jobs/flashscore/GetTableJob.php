<?php

namespace App\Jobs\flashscore;

use App\Jobs\UpdateStandingJob;
use App\Standing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use Symfony\Component\DomCrawler\Crawler;

class GetTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $league_id;
    private $league_url;

    /**
     * Create a new job instance.
     *
     * @param $league_id
     * @param $league_url
     */
    public function __construct($league_id, $league_url)
    {
        $this->league_id = $league_id;
        $this->league_url = $league_url;
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
        $page->goto($this->league_url);

        $html = $page->evaluate(JsFunction::createWithBody('return document.documentElement.outerHTML'));

        $browser->close();

        $crawler = new Crawler($html);

        $table = $crawler->filter('#glib-stats')->eq(0);

        if (!$table->count()) {
            return false;
        }

        $table->filter('.table__row')->each(function ($teamRow) {
            $standing = [
                'team' => $teamRow->filter('.team_name_span a')->first()->text(),
                'mp' => trim($teamRow->filter('.table__cell--matches_played')->first()->text()),
                'w' => trim($teamRow->filter('.table__cell--wins_regular')->first()->text()),
                'd' => trim($teamRow->filter('.table__cell--draws')->first()->text()),
                'l' => trim($teamRow->filter('.table__cell--losses_regular')->first()->text()),
                'g' => trim($teamRow->filter('.table__cell--goals')->first()->text()),
                'pts' => trim($teamRow->filter('.table__cell--points')->first()->text()),
                'league_id' => $this->league_id,
            ];

            UpdateStandingJob::dispatch($standing);
        });
    }
}
