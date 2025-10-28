<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Contracts\CommodityRepositoryInterface;
use App\Repositories\Eloquent\CommodityRepository;
use App\Repositories\Contracts\CommodityGradeRepositoryInterface;
use App\Repositories\Eloquent\CommodityGradeRepository;
use App\Repositories\Contracts\HarvestRepositoryInterface;
use App\Repositories\Eloquent\HarvestRepository;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Eloquent\StockRepository;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Eloquent\StockMovementRepository;
use App\Repositories\Contracts\ProductionReportRepositoryInterface;
use App\Repositories\Eloquent\ProductionReportRepository;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Contracts\ShipmentRepositoryInterface;
use App\Repositories\Eloquent\ShipmentRepository;
use App\Repositories\Contracts\SalesDistributionRepositoryInterface;
use App\Repositories\Eloquent\SalesDistributionRepository;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Eloquent\ActivityLogRepository;
use App\Services\Contracts\FileUploadServiceInterface;
use App\Services\FileUploadService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind User Repository
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        // Bind Commodity Repository
        $this->app->bind(
            CommodityRepositoryInterface::class,
            CommodityRepository::class
        );

        // Bind CommodityGrade Repository
        $this->app->bind(
            CommodityGradeRepositoryInterface::class,
            CommodityGradeRepository::class
        );

        // Bind Harvest Repository
        $this->app->bind(
            HarvestRepositoryInterface::class,
            HarvestRepository::class
        );

        // Bind Stock Repository
        $this->app->bind(
            StockRepositoryInterface::class,
            StockRepository::class
        );

        // Bind StockMovement Repository
        $this->app->bind(
            StockMovementRepositoryInterface::class,
            StockMovementRepository::class
        );

        // Bind ProductionReport Repository
        $this->app->bind(
            ProductionReportRepositoryInterface::class,
            ProductionReportRepository::class
        );

        // Bind Product Repository
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        // Bind Order Repository
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );

        // Bind Shipment Repository
        $this->app->bind(
            ShipmentRepositoryInterface::class,
            ShipmentRepository::class
        );

        // Bind SalesDistribution Repository
        $this->app->bind(
            SalesDistributionRepositoryInterface::class,
            SalesDistributionRepository::class
        );

        // Bind ActivityLog Repository
        $this->app->bind(
            ActivityLogRepositoryInterface::class,
            ActivityLogRepository::class
        );

        // Bind FileUpload Service
        $this->app->singleton(
            FileUploadServiceInterface::class,
            FileUploadService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
