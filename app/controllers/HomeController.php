<?php

class HomeController extends Controller
{
    public function index()
    {
        require_once '../app/models/EventModel.php';
        $eventModel = new EventModel();
        
        $data = [
            'title' => 'Home - Internal Audit Department',
            'recent_events' => $eventModel->getRecentEvents(2),
            'upcoming_events' => $eventModel->getUpcomingEvents(2)
        ];
        $this->view('home', $data);
    }

    public function about()
    {
        $data = [
            'title' => 'About Us - Internal Audit Department'
        ];
        $this->view('about', $data);
    }

    public function contact()
    {
        $data = [
            'title' => 'Contact Us - Internal Audit Department'
        ];
        $this->view('contact', $data);
    }

    // Existing methods from registration-routes if they were intended to be here
    public function viewAbout()
    {
        $this->about();
    }

    public function viewRegistration()
    {
        // This seems to be a protected page based on registration-routes.php
        $data = [
            'title' => 'Registration'
        ];
        $this->view('registration', $data);
    }

    public function dashboard()
    {
        require_once '../app/models/EventModel.php';
        $eventModel = new EventModel();

        $data = [
            'title' => 'Admin Dashboard',
            'total_users' => 0, // Placeholder
            'active_sessions' => 0,
            'uploads' => 0,
            'recent_events' => $eventModel->getRecentEvents(2),
            'upcoming_events' => $eventModel->getUpcomingEvents(2)
        ];
        $this->view('home', $data);
    }
}
