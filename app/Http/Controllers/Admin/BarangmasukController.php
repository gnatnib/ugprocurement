<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AksesModel;
use App\Models\Admin\BarangmasukModel;
use App\Models\Admin\BarangModel;
use App\Models\Admin\CustomerModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BarangmasukController extends Controller
{
    public function index()
    {
        $data["title"] = "Barang Masuk";
        $data["hakTambah"] = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang Masuk', 'tbl_akses.akses_type' => 'create'))->count();

        return view('Admin.BarangMasuk.index', $data);
    }

    public function create(Request $request)
    {
        try {
            $data["title"] = "Tambah Barang Masuk";
            $requestId = $request->query('request_id');

            // Validate if request_id exists and is in draft status
            $requestData = DB::table('tbl_request_barang')
                ->where('request_id', $requestId)
                ->where('status', 'draft')
                ->first();

            if (!$requestData) {
                return redirect()
                    ->route('request-barang.index')
                    ->with('error', 'Request tidak valid atau sudah diproses');
            }

            // Get user data from session
            $user = Session::get('user');
            $data["hakTambah"] = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')
                ->where([
                    'tbl_akses.role_id' => $user->role_id,
                    'tbl_submenu.submenu_judul' => 'Barang Masuk',
                    'tbl_akses.akses_type' => 'create'
                ])->count();

            $data["request_data"] = $requestData;
            $data["barangs"] = BarangModel::orderBy('barang_nama', 'ASC')->get();

            return view('Admin.BarangMasuk.index', $data);
        } catch (\Exception $e) {
            Log::error('Error in create method: ' . $e->getMessage());
            return redirect()
                ->route('request-barang.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            $user = Session::get('user');
            $requestId = $request->query('request_id');

            $query = BarangmasukModel::leftJoin('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangmasuk.barang_kode')
                ->leftJoin('tbl_request_barang', 'tbl_request_barang.request_id', '=', 'tbl_barangmasuk.request_id')
                ->leftJoin('tbl_user', 'tbl_user.user_id', '=', 'tbl_barangmasuk.user_id');

            // Filter by request_id
            if ($requestId) {
                $query->where('tbl_barangmasuk.request_id', $requestId);
            }

            // Filter data berdasarkan role
            if ($user->role_id == 4) { // GM
                $query->where([
                    'tbl_user.divisi' => $user->divisi,
                    'tbl_user.departemen' => $user->departemen
                ]);
            } else {
                $query->where('tbl_barangmasuk.user_id', $user->user_id);
            }

            $data = $query->select(
                'tbl_barangmasuk.*',
                'tbl_barang.barang_nama',
                'tbl_request_barang.status as request_status'
            )
                ->orderBy('tbl_barangmasuk.request_id', 'DESC')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tgl', function ($row) {
                    return $row->bm_tanggal ? Carbon::parse($row->bm_tanggal)->translatedFormat('d F Y') : '-';
                })
                ->addColumn('barang', function ($row) {
                    return $row->barang_nama ?? '-';
                })
                ->addColumn('approval', function ($row) {
                    return $row->approval ?? 'PENDING';
                })
                ->addColumn('tracking_status', function ($row) {
                    return $row->tracking_status ?? 'PENDING';
                })
                ->addColumn('action', function ($row) {
                    $array = array(
                        "bm_id" => $row->bm_id,
                        "bm_kode" => $row->bm_kode,
                        "barang_kode" => $row->barang_kode,
                        "user_id" => $row->user_id,
                        "bm_tanggal" => $row->bm_tanggal,
                        "bm_jumlah" => $row->bm_jumlah,
                    );

                    $button = '';
                    $hakEdit = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')
                        ->where([
                            'tbl_akses.role_id' => Session::get('user')->role_id,
                            'tbl_submenu.submenu_judul' => 'Barang Masuk',
                            'tbl_akses.akses_type' => 'update'
                        ])->count();

                    $hakDelete = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')
                        ->where([
                            'tbl_akses.role_id' => Session::get('user')->role_id,
                            'tbl_submenu.submenu_judul' => 'Barang Masuk',
                            'tbl_akses.akses_type' => 'delete'
                        ])->count();

                    if ($hakEdit > 0 && $hakDelete > 0) {
                        $button .= '
                    <div class="g-2">
                        <a class="btn modal-effect text-primary btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Umodaldemo8" data-bs-toggle="tooltip" data-bs-original-title="Edit" onclick=update(' . json_encode($array) . ')><span class="fe fe-edit text-success fs-14"></span></a>
                        <a class="btn modal-effect text-danger btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Hmodaldemo8" onclick=hapus(' . json_encode($array) . ')><span class="fe fe-trash-2 fs-14"></span></a>
                    </div>';
                    } else if ($hakEdit > 0) {
                        $button .= '
                    <div class="g-2">
                        <a class="btn modal-effect text-primary btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Umodaldemo8" data-bs-toggle="tooltip" data-bs-original-title="Edit" onclick=update(' . json_encode($array) . ')><span class="fe fe-edit text-success fs-14"></span></a>
                    </div>';
                    } else if ($hakDelete > 0) {
                        $button .= '
                    <div class="g-2">
                        <a class="btn modal-effect text-danger btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Hmodaldemo8" onclick=hapus(' . json_encode($array) . ')><span class="fe fe-trash-2 fs-14"></span></a>
                    </div>';
                    } else {
                        $button .= '-';
                    }
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function proses_tambah(Request $request)
    {
        try {
            $user = Session::get('user');

            $latestRequest = DB::table('tbl_request_barang')
                ->where('user_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$latestRequest) {
                return response()->json([
                    'success' => false,
                    'title' => 'Tidak dapat menambah barang!',
                    'message' => 'Anda harus membuat request terlebih dahulu sebelum menambah barang masuk.',
                    'type' => 'warning'
                ], 400); // Using 400 Bad Request status code
            }

            $barangmasuk = BarangmasukModel::create([
                'bm_tanggal' => $request->tglmasuk,
                'bm_kode' => $request->bmkode,
                'barang_kode' => $request->barang,
                'request_id' => $latestRequest->request_id,
                'keterangan' => $request->keterangan,
                'bm_jumlah' => $request->jml,
                'harga' => $request->harga,
                'user_id' => $user->user_id,
                'divisi' => $user->divisi,
                'status' => null,
                'approval' => null,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error saving data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'title' => 'Error!',
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }


    public function proses_ubah(Request $request, BarangmasukModel $barangmasuk)
    {
        //update data
        $barangmasuk->update([
            'bm_tanggal' => $request->tglmasuk,
            'barang_kode' => $request->barang,
            'keterangan' => $request->keterangan,
            'bm_jumlah' => $request->jml,
        ]);

        return response()->json(['success' => 'Berhasil']);
    }

    public function proses_hapus(Request $request, BarangmasukModel $barangmasuk)
    {
        //delete
        $barangmasuk->delete();

        return response()->json(['success' => 'Berhasil']);
    }
}
