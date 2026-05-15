<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\ServiceTemplate;
use Illuminate\Support\Facades\Auth;

class ServiceTemplateController extends Controller
{
    private const CATEGORY_MAP = [
        'civil_registry'   => 'Civil Registry',
        'mukhtar'          => 'Mukhtar Services',
        'municipality'     => 'Municipal Permits',
        'public_health'    => 'Public Health',
        'general_security' => 'General Security',
    ];

    public function index()
    {
        $user  = Auth::user();
        $role  = $user?->role?->name;
        $query = ServiceTemplate::where('is_active', true);

        if ($role === 'office') {
            $officeType = $user->office_id
                ? Office::where('id', $user->office_id)->value('office_type')
                : null;

            $category = self::CATEGORY_MAP[$officeType] ?? null;

            if (!$category) {
                return response()->json([
                    'success' => true,
                    'data'    => [],
                    'warning' => 'Your office type is not configured. Ask an administrator to set it.',
                ]);
            }

            $query->where('category', $category);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('name_en')->get(),
        ]);
    }

    public function show(int $id)
    {
        $template = ServiceTemplate::findOrFail($id);
        return response()->json(['success' => true, 'data' => $template]);
    }
}
