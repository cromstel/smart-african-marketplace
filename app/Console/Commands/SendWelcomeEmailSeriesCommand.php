<?php

namespace App\Console\Commands;

use App\Models\Newsletter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailSeriesCommand extends Command
{
    protected $signature = 'campaigns:send-welcome-series {--max=500}';

    protected $description = 'Send welcome email series to subscribed newsletter users';

    public function handle(): int
    {
        $max = max(1, (int) $this->option('max'));

        $subscribers = Newsletter::query()
            ->where('status', 'subscribed')
            ->whereNotNull('email')
            ->orderBy('created_at')
            ->limit($max)
            ->get();

        $sent = 0;
        foreach ($subscribers as $subscriber) {
            $step = $this->determineStep($subscriber->created_at);
            if ($step === null) {
                continue;
            }

            $alreadySent = DB::table('newsletter_welcome_email_logs')
                ->where('email', $subscriber->email)
                ->where('series_step', $step)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            [$subject, $body] = $this->contentForStep($step);
            $html = '<div style="font-family:Arial,sans-serif;color:#1f2937;line-height:1.5;">';
            $html .= '<h2 style="margin:0 0 12px;">'.$subject.'</h2>';
            $html .= '<p>'.nl2br(e($body)).'</p>';
            $html .= '<p style="margin-top:16px;"><a href="'.route('shop.index').'" style="display:inline-block;background:#ea580c;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">Start Shopping</a></p>';
            $html .= '</div>';

            Mail::html($html, function ($message) use ($subscriber, $subject) {
                $message->to($subscriber->email)->subject($subject);
            });

            DB::table('newsletter_welcome_email_logs')->insert([
                'newsletter_id' => $subscriber->id,
                'email' => $subscriber->email,
                'series_step' => $step,
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sent++;
        }

        $this->info("Welcome email series complete. Sent: {$sent}");

        return self::SUCCESS;
    }

    private function determineStep($createdAt): ?int
    {
        $days = now()->diffInDays($createdAt);

        return match (true) {
            $days >= 7 => 3,
            $days >= 3 => 2,
            $days >= 0 => 1,
            default => null,
        };
    }

    private function contentForStep(int $step): array
    {
        return match ($step) {
            1 => [
                 'Welcome to ' . config('app.name'),
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
