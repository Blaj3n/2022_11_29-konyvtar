<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index()
    {
        $books =  Book::all();
        return $books;
    }

    public function show($id)
    {
        $book = Book::find($id);
        return $book;
    }
    public function destroy($id)
    {
        Book::find($id)->delete();
    }
    public function store(Request $request)
    {
        $Book = new Book();
        $Book->author = $request->author;
        $Book->title = $request->title;
        $Book->save();
    }

    public function update(Request $request, $id)
    {
        $Book = Book::find($id);
        $Book->author = $request->author;
        $Book->title = $request->title;
    }

    public function bookCopies($title)
    {
        $copies = Book::with('copy_c')->where('title', '=', $title)->get();
        return $copies;
    }

    /*Csoportosítsd szerzőnként a könyveket (nem példányokat) a szerzők ABC szerinti növekvő sorrendjében!*/
    public function authors()
    {
        $books = DB::table('books')
            ->select('author', 'title')
            ->orderBy('author')
            ->get();

        return $books;
    }
    /*Határozd meg a könyvtár nyilvántartásában legalább 2 könyvvel rendelkező szerzőket*/
    public function authors_min($number)
    {
        $books = DB::table('books')
            ->selectRaw('author, count(*)')
            ->groupBy('author')
            ->having('count(*)', '>=', $number)
            ->get();

        return $books;
    }
    /*A B betűvel kezdődő szerzőket add meg! */
    public function authorsB($char)
    {
        $authors = DB::table('books')
            ->select('author')
            ->whereRaw("author LIKE '${char}%'")
            ->get();
        return $authors;
    }

    public function authorListChar($char)
    {
        $authors = DB::table('books')
            ->select('author')
            ->whereRaw("author LIKE ,${char}'%'")
            ->get();
        return $authors;
    }
    /*A bejelentkezett felhasználó 3 napnál régebbi előjegyzéseit add meg! (együtt) */
    public function older($day)
    {
        $user = Auth::user();
        $reservations = DB::table('reservations as r')
            ->select('r.book_id', 'r.start')
            ->where('r.user_id', $user->id)
            ->whereRaw('DATEDIFF(CURRENT_DATE, r.start) > ?', $day)
            ->get();

        return $reservations;
    }
    /*Bejelentkezett felhasználó azon kölcsönzéseit add meg (copy_id és db), ahol egy példányt legalább db-szor (paraméteres fg) kölcsönzött ki! (együtt) */
    public function moreLendings($db)
    {
        $user = Auth::user();
        $lendings = DB::table('lendings')
            ->selectRaw('count(copy_id) as number_of_copies, copy_id')
            ->where('user_id', $user->id)
            ->groupBy('copy_id')
            ->having('number_of_copies', '>=', $db)
            ->get();

        return $lendings;
    }
    /*Hosszabbítsd meg a könyvet, ha nincs rá előjegyzés! (együtt) */
    public function lengthen($copy_id, $start)
    {
        //könyv meghosszabbítása
        $user = Auth::user();
        //könyv lekérdezése
        $book = DB::table('lendings as l')
            ->select('c.book_id')
            ->join('copies as c', 'l.copy_id', '=', 'c.copy_id') //kapcsolat
            ->where ('l.user_id', $user_id)     //esetleges szűrés
            ->where ('l.copy_id', $copy_id)
            ->where ('l.start', $start)
            ->get()
            ->value('book_id');

            //return $book;
    }
}
