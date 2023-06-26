<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 10);

        $stores = Store::query();

        if ($search) {
            $stores
                ->where('stores.name', 'like', '%' . $search . '%')
                ->orWhere('stores.description', 'like', '%' . $search . '%');
        }

        $stores->with('user')->select('stores.*')->latest();

        return ResponseFormatter::success(
            $stores->paginate($limit),
            'Daftar toko berhasil ditemukan.',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'logo' => 'nullable|file',
            'banner' => 'nullable|file',
        ]);

        $user = Auth::user();

        try {
            $store = Store::create([
                'user_id' => $user->id,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'address' => $request->input('address'),
            ]);

            // Logo
            if ($request->hasFile('logo')) {
                $logo = null;
                $logo = $request->file('logo')->store('store/' . $store->id);

                $store->update([
                    'logo' => $logo,
                ]);
            }

            // Banner
            if ($request->hasFile('banner')) {
                $banner = null;
                $banner = $request->file('banner')->store('store/' . $store->id);

                $store->update([
                    'banner' => $banner,
                ]);
            }

            $store = Store::with('user')->find($store->id);

            return ResponseFormatter::success([
                'store' => $store,
            ], 'Toko berhasil ditambahkan.', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Toko gagal ditambahkan.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $store = Store::with('user')->find($id);

        if (!$store) {
            return ResponseFormatter::error('Toko tidak ditemukan.', 404);
        }

        return ResponseFormatter::success([
            'store' => $store,
        ], 'Toko berhasil ditemukan.', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'logo' => 'nullable|file',
            'banner' => 'nullable|file',
        ]);

        $store = Store::find($id);

        if (!$store) {
            return ResponseFormatter::error('Toko tidak ditemukan.', 404);
        }

        // Authorization check
        $user = Auth::user();

        if ($store->user_id != $user->id || $user->role != 'admin') {
            return ResponseFormatter::error('Anda bukan pemilik toko.', 401);
        }

        try {
            $store->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'address' => $request->input('address'),
            ]);

            // Logo
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($store->logo) {
                    Storage::delete($store->logo);
                }

                $logo = $request->file('logo')->store('store/' . $store->id);

                $store->update([
                    'logo' => $logo,
                ]);
            }

            // Banner
            if ($request->hasFile('banner')) {
                // Delete old banner
                if ($store->banner) {
                    Storage::delete($store->banner);
                }

                $banner = $request->file('banner')->store('store/' . $store->id);

                $store->update([
                    'banner' => $banner,
                ]);
            }

            $store = Store::with('user')->find($store->id);

            return ResponseFormatter::success([
                'store' => $store,
            ], 'Data toko berhasil diubah.', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Toko gagal diubah.' . $error, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return ResponseFormatter::error('Toko tidak ditemukan.', 404);
        }

        // Authorization check
        $user = Auth::user();

        if ($store->user_id != $user->id || $user->role != 'admin') {
            return ResponseFormatter::error('Anda bukan pemilik toko.', 401);
        }

        $store->delete();

        return ResponseFormatter::success(
            $store->id,
            'Toko berhasil dihapus.',
            200
        );
    }
}
