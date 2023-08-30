<?php

namespace App\Console\Commands;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PostStatusUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update post status command after 2 weeks';

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
        $twoWeeksAgo = Carbon::now()->subWeeks(2);

        $postsToHide = Post::where('status', true)
            ->where('created_at', '<', $twoWeeksAgo)
            ->get();

        foreach ($postsToHide as $post) {
            $post->update(['status' => 1]);

            // Send notification to the poster
            $poster = $post->user; // Assuming a user relationship on the Post model
            $notification = new \Notification([
                'user_id' => $poster->id,
                'message' => 'Your post has been hidden due to inactivity. You can display it again.',
            ]);
            $notification->save();
        }

        $threeWeeks = Carbon::now()->subWeeks(3);

        $postsToHide = Post::where('status', true)
            ->where('created_at', '<', $threeWeeks)
            ->get();

        foreach ($postsToHide as $post) {
            $post->delete();

            // Send notification to the poster
            $poster = $post->user; // Assuming a user relationship on the Post model
            $notification = new \Notification([
                'user_id' => $poster->id,
                'message' => 'Your post has been deleted due to time expired.',
            ]);
            $notification->save();
        }
    }
}