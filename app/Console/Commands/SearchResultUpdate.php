<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\PostController;
use App\posts;
use Illuminate\Console\Command;

class SearchResultUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This commnad update search result';

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
        $postController = new PostController;
        // $post
        // $post = posts::first();
        $allPost = posts::all();
        foreach ($allPost as $key => $post) {
        $updatesearch = $postController->SearchResultUpdate($post);
        // dd($updatesearch);
        $this->info('Update search index for  - '. $updatesearch->title);

        }

    }
}
