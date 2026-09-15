<?php

namespace App\Jobs;

use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Roadmap 3.2: previously sent inline inside SendWelcomeEmailSeriesCommand's
 * foreach loop over up to 500 subscribers per run. One mail failure aborted
 * the whole batch. Each subscriber/step pair is now its own retryable job.
 */
class SendWelcomeSeriesEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public Newsletter $subscriber, public int $step)
    {
        $this->onQueue('campaigns');
    }

    /**
     * Re-checks the "already sent this step" guard right before sending --
     * not just at dispatch time -- so a delayed job can't duplicate a step
     * that another process (or a re-run of the command) already logged.
     */
    public function handle(): void
    {
        $alreadySent = DB::table('newsletter_welcome_email_logs')
            ->where('email', $this->subscriber->email)
            ->where('series_step', $this->step)
            ->exists();

        if ($alreadySent) {
            return;
        }

        [$subject, $body] = $this->contentForStep($this->step);
        $html = '<div style="font-family:Arial,sans-serif;color:#1f2937;line-height:1.5;">';
        $html .= '<h2 style="margin:0 0 12px;">'.$subject.'</h2>';
        $html .= '<p>'.nl2br(e($body)).'</p>';
        $html .= '<p style="margin-top:16px;"><a href="'.route('shop.index').'" style="display:inline-block;background:#ea580c;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">Start Shopping</a></p>';
        $html .= '</div>';

        Mail::html($html, function ($message) use ($subject) {
            $message->to($this->subscriber->email)->subject($subject);
        });

        DB::table('newsletter_welcome_email_logs')->insert([
            'newsletter_id' => $this->subscriber->id,
            'email' => $this->subscriber->email,
            'series_step' => $this->step,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function contentForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'Welcome to '.config('app.name'),
                'Thanks for subscribing. Discover authentic products and services from trusted vendors.',
            ],
            2 => [
                'Top categories to explore',
                "Here are popular categories shoppers love:\n- Food & Groceries\n- Fashion & Clothing\n- Beauty & Salons",
            ],
            default => [
                'Exclusive offer for subscribers',
                'You are part of our community. Watch for upcoming promotions and limited-time deals.',
            ],
        };
    }
}
