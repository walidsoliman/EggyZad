<?php

namespace App\Http\Controllers;
use App\Auction;
use Illuminate\Http\Request;
use function PHPSTORM_META\type;
use App\Product;
use App\Favauc;
use Auth;
use App\Auc_buyer;
use Illuminate\Notifications\Notifiable;
use App\Notifications\NewBid;
use App\User;
use function Symfony\Component\VarDumper\Tests\Fixtures\bar;
use Illuminate\Support\Facades\Gate;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {



        $n = date('Y-m-d') . " " . date("h:i:s");
        // dd($n>Auction::find(7)['End_Time']);

        $endAuc = Auction::All()->where('End_Time', '<=', $n)->take(5);
        $currentAuc = Auction::All()->where('Start_Time', '<=', $n)->where('End_Time', '>', $n)->take(5);
        $futureAuc = Auction::All()->where('Start_Time', '>', $n)->take(5);
        $notifs=null;
            if(Auth::User())
         $bidnotifis=Auth::User()->unreadNotifications;
        return view('welcome', compact('endAuc', 'currentAuc', 'futureAuc','bidnotifis'));
    }

    public function show($id)
    {
        // Auth::logout();


        $n = date('Y-m-d') . " " . date("h:i:s");


        $auc = Auction::find($id);



            if ($auc != null && ($auc->Start_Time <= $n && $auc->End_Time > $n)) {
                $auc_byers = Auc_buyer::where('auction_id', '=', $id)->orderBy('price', 'desc')->get();


                $notifs = null;
                if (Auth::User())
                    $bidnotifis = Auth::User()->unreadNotifications;


                return view('Auction', compact('auc', 'auc_byers', 'bidnotifis'));
            } else
                return redirect('/');

        }

    public function Bid($id, Request $request)
    {


        $auc = Auction::find($id);
        if (!$auc)
            return redirect('/');

         //


        if ($request->ajax()) {

                if (Gate::allows('bid', $auc)) {
                    $CurMax = $auc->current_max_Price;
                    if ($request['Price'] <= $CurMax)
                        return response("Enter Number More Than $ " . $CurMax);
                    else {

                        $auc->current_max_Price = $request['Price'];
                        $auc->save();

                        $notif = Auc_buyer::Create([


                            'auction_id' => $id,
                            'user_id' => Auth::user()->id,
                            'price' => $request['Price']


                        ]);
                        $othersUsersid = Auc_buyer::select('user_id')->where('user_id', '!=', Auth::user()->id)->distinct()->get();

                        foreach ($othersUsersid as $user) {


                            User::find($user->user_id)->notify(new NewBid($auc));

                        }
                        $bidnotifis = Auth::User()->unreadNotifications;

                        //Auth::User()->notify(new InvoicePaid("new Bid"));

                        $data = array(

                            'username' => Auth::user()->username,

                            'price' => $auc->current_max_Price,
                            'notif' => $notif


                        );

                        //  $auc_byers = Auc_buyer::where('auction_id', '=', $id)->orderBy('price', 'desc')->get();
                        return $data;


                    }
                }


               else
                   return response("You can't Bid More Than 5 times for every Auction In Free Account" );
            }


        }
        //else
       // return back()->with('bidLimit');

    //}

    public  function  rem($id)
         {


             Favauc::Create([

                 'user_id'=>Auth::User()->id,
                 'auction_id'=>$id,






             ]);

  return redirect('/');


         }
    public  function  upnotif()
         {

             Auth::User()->unreadNotifications->markAsRead();

         }
    public function  update($id)
    {
      //return 1;

        return Auction::find($id)->current_max_Price;
    }

}
