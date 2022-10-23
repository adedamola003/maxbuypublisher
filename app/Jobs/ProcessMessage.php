<?php

namespace App\Jobs;

use App\Models\Publisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $messageId;
    public $subscriber;
    public Publisher $publisher;
    /**
     * @var string[]
     */
    public array $header;
    public $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($thisPublisher, $subscriber, $messageId, $message)
    {
        $this->messageId = $messageId;
        $this->subscriber = $subscriber;
        $this->publisher = $thisPublisher;
        $this->header = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
        $this->message = $message;
    }




    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //send message to subscriber
        $postData = ['message' => $this->message, 'created_at' => date('Y-m-d H:i:s')];
        Http::acceptJson()->withHeaders($this->header)->post($this->subscriber->endpoint, $postData);

        //log message
        //$thisPublisher = new Publisher();
        $this->publisher::create([
            'message_id' => $this->messageId,
            'subscriber_id' => $this->subscriber->id
        ]);
    }
}
