$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

Write-Host "==> Setting up Multi-Currency Payments"

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example"
}

if (-not (Test-Path "backend\.env")) {
    Copy-Item "backend\.env.example" "backend\.env"
    Write-Host "Created backend/.env from backend/.env.example"
}

Write-Host "==> Starting Docker services..."
docker compose up -d --build

Write-Host "==> Waiting for MySQL..."
Start-Sleep -Seconds 10

Write-Host "==> Bootstrapping Laravel..."
docker compose exec backend php artisan key:generate --force
docker compose exec backend php artisan migrate --seed

Write-Host ""
Write-Host "Setup complete!"
Write-Host "  App:      http://localhost:8080"
Write-Host "  API docs: http://localhost:8080/docs/api"
Write-Host ""
Write-Host "Login: finance@buzzvel.com / password"
