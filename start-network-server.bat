@echo off
echo ========================================
echo African Marketplace - Network Server
echo ========================================
echo.
echo Starting Laravel development server...
echo Access from this computer: http://localhost:8000
echo Access from network devices: http://192.168.100.32:8000
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php artisan serve --host=0.0.0.0 --port=8000
