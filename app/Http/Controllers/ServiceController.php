<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTemplate;
use App\Models\DocumentType;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    private const TEMPLATE_CATEGORY_TO_SERVICE_CATEGORY = [
        'Civil Registry'   => 'Civil Registry',
        'Mukhtar Services' => 'Mukhtar Services',
        'Municipal Permits'=> 'Municipal Permits',
        'Public Health'    => 'Public Health',
        'General Security' => 'General Security',
    ];

    private function normalizeDocName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }

    private function defaultServiceTypeId(?string $templateCategory = null): ?int
    {
        $map = [
            'Municipal Permits' => 'Application',
            'Civil Registry'    => 'Request',
            'Mukhtar Services'  => 'Request',
            'Public Health'     => 'Request',
            'General Security'  => 'Request',
        ];

        $typeName = $templateCategory ? ($map[$templateCategory] ?? 'Request') : 'Application';

        $type = ServiceType::all()->firstWhere('name', $typeName);
        if ($type) {
            return $type->id;
        }

        return optional(ServiceType::all()->sortBy('id')->first())->id;
    }

    private function resolveRequiredDocuments(Service $service)
    {
        $requiredDocuments = $service->documentTypes;

        if ($requiredDocuments->isNotEmpty()) {
            return $requiredDocuments;
        }

        $template = ServiceTemplate::all()->first(function ($template) use ($service) {
            return $template->category === $service->category?->name
                && $template->name_en === $service->name;
        });

        if (!$template) {
            return $requiredDocuments;
        }

        $normalizedTemplateDocs = collect($template->required_documents ?? [])
            ->map(fn($name) => $this->normalizeDocName((string) $name));

        return DocumentType::all()
            ->filter(fn($doc) => $normalizedTemplateDocs->contains($this->normalizeDocName((string) $doc->name)))
            ->values();
    }

    public function index()
    {
        $services = Service::with(['category', 'office', 'documentTypes'])->get()->map(function ($service) {
            $requiredDocuments = $this->resolveRequiredDocuments($service);

            return [
                'id'                     => $service->id,
                'name'                   => $service->name,
                'service_category_id'    => $service->service_category_id,
                'service_type_id'        => $service->service_type_id,
                'office_id'              => $service->office_id,
                'fee'                    => $service->fee,
                'estimated_time'         => $service->estimated_time,
                'description'            => $service->description,
                'category'               => $service->category,
                'office'                 => $service->office,
                'documentTypes'          => $service->documentTypes,
                'document_types'         => $service->documentTypes,
                'required_documents'     => $requiredDocuments,
                'required_documents_count'=> $requiredDocuments->count(),
            ];
        });
        return response()->json(['success' => true, 'data' => $services], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $role = $user->role?->name;

        if (!in_array($role, ['admin', 'office'], true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'service_type_id'     => 'nullable|exists:service_types,id',
            'office_id'           => 'nullable|exists:offices,id',
            'fee'                 => 'required|numeric|min:0',
            'estimated_time'      => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'document_type_ids'   => 'nullable|array',
            'document_type_ids.*' => 'exists:document_types,id',
            'template_id'         => 'nullable|exists:service_templates,id',
        ]);

        // Office users always create services in their own office.
        if ($role === 'office') {
            if (!$user->office_id) {
                return response()->json(['message' => 'Your account is not linked to an office.'], 422);
            }
            $validated['office_id'] = (int) $user->office_id;
        } elseif (empty($validated['office_id'])) {
            return response()->json(['message' => 'office_id is required.'], 422);
        }

        // Template mode: lock all template-origin fields and only allow fee/description from UI.
        if (!empty($validated['template_id'])) {
            $template = ServiceTemplate::findOrFail((int) $validated['template_id']);

            $categoryName = self::TEMPLATE_CATEGORY_TO_SERVICE_CATEGORY[$template->category] ?? $template->category;
            $serviceCategoryId = ServiceCategory::query()->where('name', $categoryName)->value('id');
            if (!$serviceCategoryId) {
                return response()->json(['message' => 'Template category is not configured in service categories.'], 422);
            }

            $validated['name'] = $template->name_en;
            $validated['service_category_id'] = $serviceCategoryId;
            $validated['estimated_time'] = ($template->estimated_days ?? 1) == 1
                ? '1 business day'
                : ((int) ($template->estimated_days ?? 1)) . ' business days';
            $validated['service_type_id'] = $this->defaultServiceTypeId($template->category);

            $templateDocNames = array_map(fn($n) => $this->normalizeDocName((string) $n), $template->required_documents ?? []);
            $docMap = DocumentType::query()->get()->mapWithKeys(function ($dt) {
                return [$this->normalizeDocName((string) $dt->name) => $dt->id];
            });
            $validated['document_type_ids'] = array_values(array_unique(array_filter(array_map(function ($docName) use ($docMap) {
                return $docMap[$docName] ?? null;
            }, $templateDocNames))));
        }

        $validated['service_type_id'] = $validated['service_type_id']
            ?? $this->defaultServiceTypeId();

        if (!$validated['service_type_id']) {
            return response()->json(['message' => 'No service types are configured.'], 422);
        }

        $service = Service::create($validated);

        if ($request->has('document_type_ids')) {
            $service->documentTypes()->sync($request->document_type_ids ?? []);
        }

        return response()->json(['success' => true, 'data' => $service->load(['category', 'office', 'documentTypes'])], 201);
    }

    public function show(int $id)
    {
        $service = Service::with(['category', 'office', 'documentTypes'])->findOrFail($id);
        $requiredDocuments = $this->resolveRequiredDocuments($service);

        return response()->json(['success' => true, 'data' => array_merge($service->toArray(), [
            'required_documents'      => $requiredDocuments,
            'required_documents_count' => $requiredDocuments->count(),
        ])], 200);
    }

    public function update(Request $request, int $id)
    {
        $user    = Auth::user();
        $role    = $user->role?->name;
        $service = Service::findOrFail($id);

        if (!in_array($role, ['admin', 'office'], true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($role === 'office' && $service->office_id !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'service_type_id'     => 'nullable|exists:service_types,id',
            'office_id'           => 'nullable|exists:offices,id',
            'fee'                 => 'required|numeric|min:0',
            'estimated_time'      => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'document_type_ids'   => 'nullable|array',
            'document_type_ids.*' => 'exists:document_types,id',
        ]);

        if ($role === 'office') {
            if (!$user->office_id) {
                return response()->json(['message' => 'Your account is not linked to an office.'], 422);
            }
            $validated['office_id'] = (int) $user->office_id;
        } elseif (empty($validated['office_id'])) {
            return response()->json(['message' => 'office_id is required.'], 422);
        }

        $validated['service_type_id'] = $validated['service_type_id']
            ?? $service->service_type_id
            ?? $this->defaultServiceTypeId();

        if (!$validated['service_type_id']) {
            return response()->json(['message' => 'No service types are configured.'], 422);
        }

        $service->update($validated);

        if ($request->has('document_type_ids')) {
            $service->documentTypes()->sync($request->document_type_ids ?? []);
        }

        return response()->json(['success' => true, 'data' => $service->load(['category', 'office', 'documentTypes'])], 200);
    }

    public function destroy(int $id)
    {
        $user    = Auth::user();
        $role    = $user->role?->name;
        $service = Service::findOrFail($id);

        if (!in_array($role, ['admin', 'office'], true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($role === 'office' && $service->office_id !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $service->delete();
        return response()->json(['success' => true, 'message' => 'Service deleted.'], 200);
    }

    public function attachRequiredDocument(Request $request, int $serviceId)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        $service = Service::findOrFail($serviceId);
        $service->documentTypes()->syncWithoutDetaching([$validated['document_type_id']]);

        return response()->json(['success' => true, 'data' => $service->load('documentTypes')], 200);
    }

    public function removeRequiredDocument(int $serviceId, int $documentTypeId)
    {
        $service = Service::findOrFail($serviceId);
        $service->documentTypes()->detach($documentTypeId);

        return response()->json(['success' => true, 'data' => $service->load('documentTypes')], 200);
    }

    public function getRequiredDocuments(int $serviceId)
    {
        $service = Service::with(['documentTypes', 'category'])->findOrFail($serviceId);

        $requiredDocuments = $service->documentTypes;

        if ($requiredDocuments->isEmpty()) {
            $template = ServiceTemplate::all()->first(function ($template) use ($service) {
                return $template->category === $service->category?->name
                    && $template->name_en === $service->name;
            });

            if ($template) {
                $normalizedTemplateDocs = collect($template->required_documents ?? [])
                    ->map(fn($name) => $this->normalizeDocName((string) $name));

                $requiredDocuments = DocumentType::all()
                    ->filter(fn($doc) => $normalizedTemplateDocs->contains($this->normalizeDocName((string) $doc->name)))
                    ->values();
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'service_id'         => $service->id,
                'service_name'       => $service->name,
                'required_documents' => $requiredDocuments,
            ],
        ]);
    }
}
