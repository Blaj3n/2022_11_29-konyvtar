<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Lending;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CopyController extends Controller
{
    //
    public function index(){
        $copies =  Copy::all();
        return $copies;
    }
    
    public function show($id)
    {
        $copies = Copy::find($id);
        return $copies;
    }
    public function destroy($id)
    {
        Copy::find($id)->delete();
    }
    public function store(Request $request)
    {
        $copy = new Copy();
        $copy->book_id = $request->book_id;
        $copy->hardcovered = $request->hardcovered;
        $copy->publication = $request->publication;
        $copy->status = 0;
        $copy->save(); 
    }

    public function update(Request $request, $id)
    {
        //a book_id ne változzon! mert akkor már másik példányról van szó
        $copy = Copy::find($id);
        $copy->hardcovered = $request->hardcovered;
        $copy->publication = $request->publication;
        $copy->status = $request->status;
        $copy->save();        
    }

    public function copies_pieces($title)
    {	
        $copies = Book::with('copy_c')->where('title','=', $title)->count();
        return $copies;
    }

    //view-k:

    public function newView()
    {
        //új rekord(ok) rögzítése
        $books = Book::all();
        return view('copy.new', ['books' => $books]);
    }

    public function editView($id)
    {
        $books = Book::all();
        $copy = Copy::find($id);
        return view('copy.edit', ['books' => $books, 'copy' => $copy]);
    }

    public function listView()
    {
        $copies = Copy::all();
        //copy mappában list blade
        return view('copy.list', ['copies' => $copies]);
    }
    //Hány darab példány van egy adott című könyvből?
    public function bookCopyCount($title){
        $copies = DB::table('copies as c')
        ->join('books as b' ,'c.book_id','=','b.book_id') 
        ->where('b.title','=', $title)
        ->count();			
        return $copies;
    }
    //dd meg a keménykötésű példányokat szerzővel és címmel! 
    public function hardcoveredCopies($hardcovered){
        $copies = DB::table('copies as c')
        ->select('b.author', 'b.title')
        ->join('books as b' ,'c.book_id','=','b.book_id') 
        ->where('c.hardcovered','=', $hardcovered)
        ->get();
        return $copies;
    }
    //Bizonyos évben kiadott példányok névvel és címmel kiíratása.
    public function givenYear($year){
        $copies = DB::table('copies as c')
        ->select('b.author', 'b.title')
        ->join('books as b' ,'c.book_id','=','b.book_id') 
        ->where('c.publication', '=',$year)
        ->get();
        return $copies;
    }
    //Raktárban lévő példányok száma.
    public function inStock($status){
        $copies = DB::table('copies as c')
        ->select('b.author', 'b.title')
        ->where('c.status', '=', $status)
        ->count();
        return $copies;
    }
    //Bizonyos évben kiadott, bizonyos könyv raktárban lévő darabjainak a száma.
    public function bookCheck($book, $year){
        $copies = DB::table('copies as c')
        ->select('b.author', 'b.title')
        ->join('books as b' ,'c.book_id','=','b.book_id') 
        ->where('b.book_id', '=',$book)
        ->where('c.publication', '=',$year)
        ->where('c.status', '=', 0)
        ->count();
        return $copies;
    }
    //Adott könyvhöz tartozó példányok kölcsönzési adatai (with-del és DB-vel is).
    public function lendingsDataDB($book){
        $lending = DB::table('copies as c')
        ->select('l.user_id','l.copy_id','l.start')
        ->join('books as b' ,'c.book_id','=','b.book_id') 
        ->join('lendings as l', 'c.copy_id', '=','l.copy_id')
        ->where('b.book_id', '=',$book)
        ->get();
        return $lending;
    }
    public function lendingsDataWT($book){  
        $lending = Copy::with('lending_c')->where('book_id','=',$book)->get();
        return $lending;
    }
}
