<?php

namespace App\Http\Controllers;

use App\Models\Exchanger;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function edit(Exchanger $platform)
    {
        return view('admin.exchanger.edit', compact('platform'));
    }

    public function update(Request $request, Exchanger $platform)
    {
        $data = $request->validate([
            'title' => 'required|string|max:128|unique:platforms,title,'.$platform->id,
        ]);
        $platform->update($data);
        return redirect()->route('view.exchangers')
            ->with('success', 'Платформа обновлена');
    }

    public function destroy(Exchanger $platform)
    {
        $platform->delete();
        return redirect()->route('view.exchangers')
            ->with('success', 'Платформа удалена');
    }
}
