<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\User;

class ProformaApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $proforma;
    protected $application;
    protected $applicant;
    protected $currentCount;
    protected $totalRequired;

    /**
     * Create a new notification instance.
     */
    public function __construct(Proforma $proforma, ProformaApplication $application, User $applicant, int $currentCount = 0, int $totalRequired = 0)
    {
        $this->proforma = $proforma;
        $this->application = $application;
        $this->applicant = $applicant;
        $this->currentCount = $currentCount;
        $this->totalRequired = $totalRequired;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $role = ucfirst($this->applicant->role);
        $amount = $this->application->amount ? 'ETB ' . number_format($this->application->amount, 2) : 'Not specified';

        $progress = $this->totalRequired > 0
            ? "{$this->currentCount}/{$this->totalRequired}"
            : (string) $this->currentCount;

        $message = (new MailMessage)
            ->subject("Application Received ({$progress}) – Proforma #{$this->proforma->file_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A new application has been received for your proforma #{$this->proforma->file_number}.");

        if ($this->totalRequired > 0) {
            $remaining = $this->totalRequired - $this->currentCount;
            if ($remaining > 0) {
                $message->line("Progress: {$this->currentCount} of {$this->totalRequired} applications received. {$remaining} more needed.");
            } else {
                $message->line("Progress: {$this->currentCount} of {$this->totalRequired} – All required applications received! Proforma is now complete.");
            }
        }

        $message->line("Customer: {$this->proforma->customer_name}")
            ->line("Vehicle: {$this->proforma->brand->name} {$this->proforma->model} ({$this->proforma->year})")
            ->action('View Application Details', url("/proforma-details?proforma_id={$this->proforma->id}"))
            ->line('Thank you for using our platform!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $role = ucfirst($this->applicant->role);
        $amount = $this->application->amount ? 'ETB ' . number_format($this->application->amount, 2) : 'Not specified';
        
        $progress = $this->totalRequired > 0
            ? "{$this->currentCount}/{$this->totalRequired}"
            : (string) $this->currentCount;

        return [
            'type' => 'proforma_application_received',
            'title' => "New {$role} Application Received ({$progress})",
            'message' => "{$this->applicant->name} submitted a price quote of {$amount} for proforma #{$this->proforma->file_number} ({$progress})",
            'proforma_id' => $this->proforma->id,
            'proforma_file_number' => $this->proforma->file_number,
            'application_id' => $this->application->id,
            'applicant_id' => $this->applicant->id,
            'applicant_name' => $this->applicant->name,
            'applicant_role' => $this->applicant->role,
            'amount' => $this->application->amount,
            'current_count' => $this->currentCount,
            'total_required' => $this->totalRequired,
            'customer_name' => $this->proforma->customer_name,
            'vehicle_info' => "{$this->proforma->brand->name} {$this->proforma->model} ({$this->proforma->year})",
            'timestamp' => now()->toISOString(),
        ];
    }
} 