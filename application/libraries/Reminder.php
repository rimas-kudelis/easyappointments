<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Reminder library.
 *
 * Handles appointment reminder functionality.
 *
 * @package Libraries
 *
 * @property Appointments_model $appointments_model
 * @property Notifications $notifications
 */
class Reminder
{
    protected EA_Controller|CI_Controller $CI;

    public function __construct()
    {
        $this->CI = &get_instance();

        $this->CI->load->model('appointments_model');
        $this->CI->load->model('providers_model');
        $this->CI->load->model('customers_model');
        $this->CI->load->model('services_model');

        $this->CI->load->library('notifications');
    }

    public function remind(): void
    {
        $start_date = date('Y-m-d H:i:s', strtotime('tomorrow 00:00:00'));

        $end_date = date('Y-m-d H:i:s', strtotime('tomorrow 23:59:59'));

        $appointments = $this->CI->appointments_model->get([
            'start_datetime >=' => $start_date,
            'end_datetime <=' => $end_date,
        ]);

        foreach ($appointments as $appointment) {
            $provider = $this->CI->providers_model->find($appointment['id_users_provider']);
            $customer = $this->CI->customers_model->find($appointment['id_users_customer']);
            $service = $this->CI->services_model->find($appointment['id_services']);

            $settings = [
                'company_name' => setting('company_name'),
                'company_link' => setting('company_link'),
                'company_email' => setting('company_email'),
                'company_color' => !empty($company_color) && $company_color != DEFAULT_COMPANY_COLOR ? $company_color : null,
                'date_format' => setting('date_format'),
                'time_format' => setting('time_format'),
            ];

            $this->CI->notifications->remind_pending_appointment($appointment, $service, $provider, $customer, $settings);
        }
    }
}
