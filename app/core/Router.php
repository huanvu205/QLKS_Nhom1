<?php

class Router
{
    private array $routes = [
        'login' => [AuthController::class, 'login'],
        'forgot-password' => [AuthController::class, 'forgotPassword'],
        'logout' => [AuthController::class, 'logout'],
        'dashboard' => [RoomController::class, 'dashboard'],
        'password' => [AuthController::class, 'password'],
        'room-types' => [RoomController::class, 'roomTypes'],
        'rooms' => [RoomController::class, 'rooms'],
        'customers' => [CustomerController::class, 'index'],
        'booking' => [BookingController::class, 'create'],
        'booking-list' => [BookingController::class, 'index'],
        'check-in' => [BookingController::class, 'checkIn'],
        'check-out' => [BookingController::class, 'checkOut'],
        'services' => [RoomController::class, 'services'],
        'service-usage' => [BookingController::class, 'serviceUsage'],
        'invoices' => [BookingController::class, 'invoices'],
        'invoice-word' => [BookingController::class, 'invoiceWord'],
        'invoice-excel' => [BookingController::class, 'invoiceExcel'],
        'reports' => [BookingController::class, 'reports'],
        'reports-excel' => [BookingController::class, 'reportsExcel'],
        'accounts' => [AuthController::class, 'accounts'],
    ];

    public function dispatch(string $page): void
    {
        $target = $this->routes[$page] ?? $this->routes['dashboard'];
        [$class, $method] = $target;

        $controller = new $class();
        $controller->$method();
    }
}
