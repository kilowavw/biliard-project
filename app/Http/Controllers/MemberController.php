<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MemberController extends Controller
{
    /**
     * Display a listing of the members.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $membersQuery = Member::orderBy('created_at', 'desc');

        if ($search) {
            $membersQuery->where(function($query) use ($search) {
                $query->where('nama_member', 'like', '%' . $search . '%')
                      ->orWhere('kode_member', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('no_telepon', 'like', '%' . $search . '%');
            });
        }

        if ($status && $status !== 'all') {
            $membersQuery->where('status_keanggotaan', $status);
        }

        $members = $membersQuery->paginate(10); // Pagination 10 member per halaman

        // Data default untuk form CREATE modal
        $defaultCreateTanggalDaftar = now()->format('Y-m-d');
        $defaultCreateTanggalKadaluarsa = now()->addMonth()->format('Y-m-d');
        $defaultCreateKodeMember = Member::generateUniqueKodeMember();

        return view('members.index', compact(
            'members', 'search', 'status',
            'defaultCreateTanggalDaftar', 'defaultCreateTanggalKadaluarsa', 'defaultCreateKodeMember'
        ));
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_member' => 'required|string|max:255',
            'kode_member' => 'required|string|unique:members,kode_member|max:255',
            'email' => 'nullable|email|unique:members,email|max:255',
            'no_telepon' => 'nullable|string|max:20',
            'tanggal_daftar' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after_or_equal:tanggal_daftar',
            'status_keanggotaan' => 'required|string|in:Aktif,Nonaktif,Expired',
            'diskon_persen' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            // Jika validasi gagal, kembalikan ke halaman index dengan error dan tampilkan modal
            return redirect()->route('members.index')->withErrors($validator, 'createMember')->withInput()->with('showCreateModal', true);
        }

        Member::create($request->all());

        return redirect()->route('members.index')->with('success', 'Member berhasil ditambahkan!');
    }

    /**
     * Display the specified member.
     */
    public function show(Member $member)
    {
        return view('members.show', compact('member'));
    }

    /**
     * Update the specified member in storage.
     */
    public function update(Request $request, Member $member)
    {
        $validator = Validator::make($request->all(), [
            'nama_member' => 'required|string|max:255',
            'kode_member' => 'required|string|unique:members,kode_member,' . $member->id . '|max:255',
            'email' => 'nullable|email|unique:members,email,' . $member->id . '|max:255',
            'no_telepon' => 'nullable|string|max:20',
            'tanggal_daftar' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after_or_equal:tanggal_daftar',
            'status_keanggotaan' => 'required|string|in:Aktif,Nonaktif,Expired',
            'diskon_persen' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
             // Jika validasi gagal, kembalikan ke halaman index dengan error dan tampilkan modal edit
            return redirect()->route('members.index')->withErrors($validator, 'editMember')->withInput()->with('showEditModal', $member->id);
        }

        $member->update($request->all());

        return redirect()->route('members.index')->with('success', 'Data member berhasil diperbarui!');
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Member berhasil dihapus!');
    }

    /**
     * Handle membership extension (renewal).
     */
    public function extendMembership(Request $request, Member $member)
    {
        $validator = Validator::make($request->all(), [
            'extension_amount' => 'required|numeric|min:0',
            'extension_method' => 'required|string|max:50',
            'extension_months' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newExpiryDate = $member->tanggal_kadaluarsa;
        if ($newExpiryDate->isPast()) {
            $newExpiryDate = now();
        }
        $newExpiryDate = $newExpiryDate->addMonths($request->extension_months);

        $member->update([
            'tanggal_kadaluarsa' => $newExpiryDate,
            'status_keanggotaan' => 'Aktif',
            'last_payment_date' => now(),
            'last_payment_amount' => $request->extension_amount,
            'last_payment_method' => $request->extension_method,
        ]);

        return redirect()->back()->with('success', 'Keanggotaan berhasil diperpanjang hingga ' . $newExpiryDate->format('d F Y') . '.');
    }
}
