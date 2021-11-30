<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Auction;
use App\Auc_buyer;
use App\Policies\AuctionPolicy;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\User' => 'App\Policies\AuctionPolicy'
        


    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
          Gate::define('bid', function ($user, $auc) {

              $numberOfBid=Auc_buyer::where(["auction_id" => $auc->id,"user_id" => $user->id])->count();
      //dd($numberOfBid);
              if($numberOfBid<5)
                  return 1;
              else
                  return 0;



        });
        Gate::define('create','App\Policies\AuctionPolicy@create');
        //
    }
}
