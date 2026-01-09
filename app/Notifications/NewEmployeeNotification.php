<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEmployeeNotification extends Notification
{
    use Queueable;

    protected $employee;

    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Employee Added')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new employee has been added to your company.')
            ->line('Name: ' . $this->employee->first_name . ' ' . $this->employee->last_name)
            ->line('Email: ' . ($this->employee->email ?? 'Not provided'))
            ->line('Phone: ' . ($this->employee->phone ?? 'Not provided'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->first_name . ' ' . $this->employee->last_name,
        ];
    }
}
