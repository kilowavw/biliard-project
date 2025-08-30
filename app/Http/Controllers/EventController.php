<?php
namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->get();
        return view('compro.event', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required',
            'deskripsi' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048'
        ]);

        $data = $request->except('_token');

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('events', 'public');
        }

        Event::create($data);

        return redirect()->route('events.index')->with('success', 'Event berhasil ditambahkan');
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'judul' => 'required',
            'deskripsi' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048'
        ]);

        $data = $request->except(['_token', '_method']);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('events', 'public');
        }

        $event->update($data);

        return redirect()->route('events.index')->with('success', 'Event berhasil diupdate');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event berhasil dihapus');
    }
}
