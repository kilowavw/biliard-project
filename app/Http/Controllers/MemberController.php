<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Tampilkan daftar member (paginate).
     */
    public function index()
    {
        $members = Member::orderBy('nama_member', 'asc')->paginate(10);
        return view('member.index', compact('members'));
    }

     public function cekMember(Request $request)
    {
        $no_telp = $request->query('no_telp');

        $is_member = Member::where('no_telp', $no_telp)->exists();

        return response()->json(['is_member' => $is_member]);
    }

    /**
     * Form tambah member baru.
     */
    public function create()
    {
        return view('member.create');
    }

    /**
     * Simpan member baru ke database.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'nama_member'   => 'required|string|max:100',
        'no_telp'       => 'required|string|max:15|unique:members,no_telp',
        'tgl_bergabung' => 'required|date',
    ]);

    // Handle status aktif (checkbox atau toggle)
    $validated['aktif'] = $request->has('aktif') ? 1 : 0;

    Member::create($validated);

    return redirect()->route('members.index')
                     ->with('success', 'Member berhasil ditambahkan!');
}


    /**
     * Form edit member.
     */
    public function edit($id)
    {
        $member = Member::findOrFail($id);
        return view('member.edit', compact('member'));
    }

    /**
     * Update data member.
     */
    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'nama_member'   => 'required|string|max:100',
            'no_telp'       => 'required|string|max:15|unique:members,no_telp,' . $member->id_member . ',id_member',
            'tgl_bergabung' => 'required|date',
        ]);

        $member->update($validated);

        return redirect()->route('members.index')
                         ->with('success', 'Member berhasil diperbarui!');
    }

    /**
     * Hapus member.
     */
    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->delete();

        return redirect()->route('members.index')
                         ->with('success', 'Member berhasil dihapus!');
    }
}
