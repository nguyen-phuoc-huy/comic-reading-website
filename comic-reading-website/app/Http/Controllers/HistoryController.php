<?php

namespace App\Http\Controllers;

use App\Models\ReadingHistory;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    // GET /lich-su
    public function index(Request $request)
    {
        $histories = ReadingHistory::with(['comic', 'chapter'])
            ->where('user_id', $request->user()->id)
            ->latest('updated_at')
            ->paginate(20);

        return view('history.index', compact('histories'));
    }

    // DELETE /lich-su/{history}
    public function destroy(Request $request, ReadingHistory $history)
    {
        abort_unless($history->user_id === $request->user()->id, 403);

        $history->delete();

        return back()->with('success', 'Đã xóa khỏi lịch sử.');
    }

    // DELETE /lich-su
    public function clear(Request $request)
    {
        ReadingHistory::where('user_id', $request->user()->id)->delete();

        return back()->with('success', 'Đã xóa toàn bộ lịch sử đọc.');
    }
}