<?php
namespace ItmoSoft\Loyalty;
use Illuminate\Support\ServiceProvider;
class LoyaltyServiceProvider extends ServiceProvider {
 public function register(): void {
  $this->mergeConfigFrom(__DIR__.'/../config/loyalty.php', 'itmosoft-loyalty');
  $this->app->singleton(LoyaltyManager::class, fn ($app) => new LoyaltyManager($app['config']->get('itmosoft-loyalty', [])));
  $this->app->alias(LoyaltyManager::class, 'itmosoft.loyalty');
 }
 public function boot(): void {
  $this->publishes([__DIR__.'/../config/loyalty.php' => config_path('itmosoft-loyalty.php')], 'itmosoft-loyalty-config');
  $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
 }
}
