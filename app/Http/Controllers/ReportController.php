<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use Excel;
use App\Exports\ReportExport;
use App\Models\Response;

class ReportController extends Controller

{
 public function exportPDF()
 {
    //ambil data yang akan di tampilkan pada pdf, bisa juga dengan where atau eloquent lainnya dan jangan gunakan pagination
    //jangan lupa kovert data jadi array dengan toArray()
    $data = Report::with('response')->get()->toArray();
    //kiirm data yang di ambil kepada view yang akan di tampilkan,kirim dengan inisial
    view()->share('reports',$data);
    //panggil view blade yang akan di cetak pdf serta data yang akan digunakan 
    $pdf = PDF::loadView('print',$data)->setPaper('a4', 'landscape');
    //download PDF file dengan nama tertentu
    return $pdf->download('data_pengaduan_keseluruhan.pdf');
 }

 public function createdPDF($id)
 {
    //ambil data yang akan di tampilkan pada pdf, bisa juga dengan where atau eloquent lainnya dan jangan gunakan pagination
    //jangan lupa kovert data jadi array dengan toArray()
    $data = Report::with('response')->where('id', $id)->get()->toArray();
    view()->share('reports',$data);
     
    $pdf = PDF::loadView('print',$data);
    
    return $pdf->download('data_pengaduan.pdf');
 } 
 
 public function exportExcel()
 {
    //nama file yang akan terdownload
    //selain .xlsx juga bisa .csv
    $file_name = 'data_keseluruhan_pengaduan.xlsx';
    //memanggil file ReportExport dan mendownloadnya dengan nama seperti $file_name
    return Excel::download(new ReportExport, $file_name);
 }





    public function index()
    {
        
        $reports = Report::orderBy('created_at', 'DESC')->simplePaginate(2); 
        return view('index', compact('reports'));

        // $reports = Report::all(); 
        // return view('index', compact('reports'));

        // ASC : ascending -> terkecil terbesar 1-100 / a-z
        // DESC : descending -> terbesar terkecil 100-1 /z-a
    }

    //Request $request ditambahkan karna pada halaman data ada fitue search nya dan akan mengambil teks yg diinput search
    public function data(Request $request)
    {
        //ambil data yang diinput ke input name nya seacrh
        $search = $request->search;

        //where akan mencari data berdasarkan column nama
        //data yang diambil merupakan data yang 'LIKE' (terdapat) teks yang dimasukin ke input serch
        //contoh : ngisi input search dengan 'fem'
        //bakal nyari ke db yg column yg namanya ada isi 'fem' nya
        //buat nyari data teks 'LIKE'

        $reports = Report::with('response')->where('nama', 'LIKE', '%' . $search . '%')->orderBy('created_at', 'DESC')->get(); 
        return view('data', compact('reports'));
    }


    public function dataPetugas(Request $request)
    {
        $search =$request->search;

        //with : ambil relasi (nama fungsi hasOne/hasMany/ belongsTo di modelnya), ambil data dari relasi itu
        $reports = Report::with('response')->where('nama', 'LIKE', '%' . $search . '%')->orderBy('created_at', 'DESC')->get(); 
        return view('data_petugas', compact('reports'));
    }


    // Request $request untuk mengambil data
    public function auth(Request $request)
    {
        //validasi
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        // ambil data dan simpan di variable
        $user = $request->only('email', 'password');

        //simpen data ke auth dengan Auth::attempt
        //cek proses penyimpanan ke auth berhasik ato tidak lewar if else
        if (Auth::attempt($user)) {
            //nesting if, if bersrang, if didalam if, 
            //kalau data login udah masuk ke fitur Auth, dicek lagi pake if-else
            //kalo data Auth tersebut role nya admin mala masuk ke route data
            //kalau data Auth role nya petugas maka masuk ke route data.petugas
            if (Auth::user()->role == 'admin') {
                return redirect()->route('data');
            }elseif (Auth::user()->role == 'petugas') {
                return redirect()->route('data.petugas');
            }
        }else {
            return redirect()->back()->with('gagal', 'Gagal login, coba ulang lagi !');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'nama' => 'required',
            'no_telp' => 'required|max:13',
            'pengaduan' => 'required',
            'foto' => 'required|image|mimes:jpg,jpeg,png,svg',
        ]);
        
        // pindah foto ke folder public
        $path = public_path('assets/image/');
        $image = $request->file('foto');
        $imgName = rand() . '.' .$image->extension(); // foto.jpg : 1234.jpg
        $image->move($path, $imgName);

        // tambah data ke db
        Report::create([
            'nik' => $request->nik,
            'nama' => $request->nama,
            'no_telp' => $request->no_telp,
            'pengaduan' => $request->pengaduan,
            'foto' => $imgName,
        ]);

        return redirect()->back()->with('success', 'Berhasil menambahkan data !');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Report::where('id', $id)->firstOrFail();
        //$data isinya -> nik sampe foto dr pengaduan 
        //hapus foto data dr folder public : path . nama fotonya
        //nama foto nya diambil dari $data yang diatas trs ngambil dari column 'foto'
        $image = public_path('assets/image/'.$data['foto']);
        unlink($image);

        $data->delete();
        Response::where('report_id', $id)->delete();
        return redirect()->back();
    }
}
