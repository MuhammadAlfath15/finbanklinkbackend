<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Article;
use App\Models\Bank;
use App\Models\BankCategory;
use App\Models\BusinessProfile;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminContentController extends Controller
{
    public function publicAds()
    {
        return response()->json(
            Ad::query()->where('is_active', true)->orderByRaw('sort_order = 0, sort_order ASC')->orderBy('id', 'asc')->get()
                ->map(fn(Ad $ad) => $this->transformAd($ad))
        );
    }

    public function publicArticles()
    {
        return response()->json(
            Article::query()
                ->where('is_active', true)
                ->orderByRaw('sort_order = 0, sort_order ASC')
                ->orderBy('id', 'asc')
                ->get()
                ->map(fn(Article $article) => $this->transformArticle($article))
        );
    }

    public function ads()
    {
        return response()->json(
            Ad::query()->orderByRaw('sort_order = 0, sort_order ASC')->orderBy('id', 'asc')->get()
                ->map(fn(Ad $ad) => $this->transformAd($ad))
        );
    }

    public function storeAd(Request $request)
    {
        $validated = $request->validate([
            'badge'          => 'required|string|max:120',
            'title'          => 'required|string|max:180',
            'description'    => 'nullable|string',
            'cta'            => 'nullable|string|max:80',
            'image'          => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'bg_color_from'  => 'nullable|string|max:20',
            'bg_color_to'    => 'nullable|string|max:20',
            'is_active'      => 'nullable|boolean',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $validated['image_url'] = $request->file('image')->store('ads', 'public');
        unset($validated['image']);

        return response()->json($this->transformAd(Ad::create($validated)), 201);
    }

    public function updateAd(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'badge'         => 'sometimes|required|string|max:120',
            'title'         => 'sometimes|required|string|max:180',
            'description'   => 'nullable|string',
            'cta'           => 'nullable|string|max:80',
            'image'         => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'bg_color_from' => 'nullable|string|max:20',
            'bg_color_to'   => 'nullable|string|max:20',
            'is_active'     => 'nullable|boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if (!empty($ad->image_url) && !$this->isAbsoluteUrl($ad->image_url)) {
                Storage::disk('public')->delete($ad->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('ads', 'public');
            unset($validated['image']);
        }

        $ad->update($validated);
        return response()->json($this->transformAd($ad->fresh()));
    }

    public function destroyAd(Ad $ad)
    {
        $ad->delete();
        return response()->json(['message' => 'Iklan dihapus.']);
    }

    public function articles()
    {
        return response()->json(
            Article::query()
                ->orderByRaw('sort_order = 0, sort_order ASC')
                ->orderBy('id', 'asc')
                ->get()
                ->map(fn(Article $article) => $this->transformArticle($article))
        );
    }

    public function storeArticle(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:180',
            'excerpt' => 'nullable|string',
            'image_url' => 'nullable|url|max:1024',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (!$request->hasFile('image') && empty($validated['image_url'])) {
            return response()->json(['message' => 'Gambar artikel wajib diisi (upload JPG/PNG atau URL).'], 422);
        }

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('articles', 'public');
        }

        $article = Article::create($validated);
        return response()->json($this->transformArticle($article), 201);
    }

    public function updateArticle(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:180',
            'excerpt' => 'nullable|string',
            'image_url' => 'nullable|url|max:1024',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if (!empty($article->image_url) && !$this->isAbsoluteUrl($article->image_url)) {
                Storage::disk('public')->delete($article->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('articles', 'public');
        }

        $article->update($validated);
        return response()->json($this->transformArticle($article->fresh()));
    }

    public function destroyArticle(Article $article)
    {
        if (!empty($article->image_url) && !$this->isAbsoluteUrl($article->image_url)) {
            Storage::disk('public')->delete($article->image_url);
        }
        $article->delete();
        return response()->json(['message' => 'Artikel dihapus.']);
    }

    public function banks()
    {
        $rows = Bank::query()
            ->with('categories:id,name')
            ->orderByDesc('id')
            ->get()
            ->map(function (Bank $bank) {
                $arr = $bank->toArray();
                if ($bank->categories->isEmpty()) {
                    $arr['category_name'] = $bank->category ?? 'terdaftar';
                    $arr['category_ids'] = [];
                    $arr['categories'] = [];
                } else {
                    $arr['category_name'] = $bank->categories->pluck('name')->implode(', ');
                    $arr['category_ids'] = $bank->categories->pluck('id')->toArray();
                    $arr['categories'] = $bank->categories->map(function ($cat) {
                        return [
                            'id' => $cat->id,
                            'name' => $cat->name,
                        ];
                    })->toArray();
                }
                $arr['promo_image_url'] = !empty($bank->promo_image) 
                    ? ($this->isAbsoluteUrl($bank->promo_image) ? $bank->promo_image : \Storage::disk('public')->url($bank->promo_image))
                    : null;
                return $arr;
            });
        return response()->json($rows);
    }

    public function storeBank(Request $request)
    {
        $validated = $this->validateBank($request);
        $validated = $this->resolveCategoryFields($validated, $request);
        $bank = Bank::create($validated);

        $categoryIds = $request->input('category_ids');
        if (is_array($categoryIds)) {
            $bank->categories()->sync($categoryIds);
        } else if (!empty($validated['category_id'])) {
            $bank->categories()->sync([$validated['category_id']]);
        }

        $bank->load('categories');
        $arr = $bank->toArray();
        $arr['category_name'] = $bank->categories->pluck('name')->implode(', ');
        $arr['category_ids'] = $bank->categories->pluck('id')->toArray();
        $arr['categories'] = $bank->categories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name])->toArray();
        $arr['promo_image_url'] = !empty($bank->promo_image) 
            ? ($this->isAbsoluteUrl($bank->promo_image) ? $bank->promo_image : \Storage::disk('public')->url($bank->promo_image))
            : null;

        return response()->json($arr, 201);
    }

    public function updateBank(Request $request, Bank $bank)
    {
        $validated = $this->validateBank($request, true);
        $validated = $this->resolveCategoryFields($validated, $request);

        if ($request->hasFile('promo_image')) {
            if (!empty($bank->promo_image) && !$this->isAbsoluteUrl($bank->promo_image)) {
                \Storage::disk('public')->delete($bank->promo_image);
            }
            $validated['promo_image'] = $request->file('promo_image')->store('banks', 'public');
        }

        $bank->update($validated);

        if ($request->has('category_ids')) {
            $categoryIds = $request->input('category_ids');
            if (is_array($categoryIds)) {
                $bank->categories()->sync($categoryIds);
            } else {
                $bank->categories()->sync([]);
            }
        }

        $bank->load('categories');
        $arr = $bank->toArray();
        $arr['category_name'] = $bank->categories->pluck('name')->implode(', ');
        $arr['category_ids'] = $bank->categories->pluck('id')->toArray();
        $arr['categories'] = $bank->categories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name])->toArray();
        $arr['promo_image_url'] = !empty($bank->promo_image) 
            ? ($this->isAbsoluteUrl($bank->promo_image) ? $bank->promo_image : \Storage::disk('public')->url($bank->promo_image))
            : null;

        return response()->json($arr);
    }

    public function destroyBank(Bank $bank)
    {
        $bank->delete();
        return response()->json(['message' => 'Kartu bank dihapus.']);
    }

    public function usersWithDocuments()
    {
        $profiles = BusinessProfile::query()->get()->keyBy('user_id');

        $users = User::query()
            ->orderByDesc('id')
            ->get()
            ->map(function (User $user) use ($profiles) {
                $profile = $profiles->get($user->id);

                $documents = [];
                $profileKeys = [
                    'nib_path', 'npwp_path', 'ktp_path', 'kk_path', 'selfie_ktp_path',
                    'ttd_path', 'rekening_path', 'foto_usaha_path', 'kontrak_path', 'bukti_pelunasan_path',
                ];

                if ($profile) {
                    foreach ($profileKeys as $key) {
                        if (!empty($profile->$key)) {
                            $documents[] = $this->buildDocPayload($key, $profile->$key, $profile);
                        }
                    }
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'documents' => array_values($documents),
                ];
            });

        return response()->json($users);
    }

    public function bankCategories()
    {
        return response()->json(BankCategory::query()->orderBy('sort_order')->orderBy('name')->get());
    }

    public function storeBankCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:80|unique:bank_categories,name',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = BankCategory::create([
            'name' => strtolower(trim($validated['name'])),
            'slug' => Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json($category, 201);
    }

    public function updateBankCategory(Request $request, BankCategory $category)
    {
        $validated = $request->validate([
            'name' => "sometimes|required|string|max:80|unique:bank_categories,name,{$category->id}",
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['name'])) {
            $validated['name'] = strtolower(trim($validated['name']));
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        // Sinkronkan kolom category legacy di banks agar konsisten
        Bank::query()->where('category_id', $category->id)->update(['category' => $category->name]);

        return response()->json($category);
    }

    public function destroyBankCategory(BankCategory $category)
    {
        $inUse = $category->banks()->exists() || Bank::query()->where('category_id', $category->id)->exists();
        if ($inUse) {
            return response()->json(['message' => 'Kategori masih dipakai oleh kartu bank. Pindahkan dulu kartunya.'], 422);
        }
        $category->delete();
        return response()->json(['message' => 'Kategori dihapus.']);
    }

    private function validateBank(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'nama_bank' => 'required|string|max:160',
            'category' => 'nullable|string|max:80',
            'category_id' => 'nullable|exists:bank_categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:bank_categories,id',
            'nama_produk' => 'required|string|max:160',
            'bunga' => 'required|string|max:120',
            'cicilan' => 'required|string|max:120',
            'skor_kecocokan' => 'nullable|integer|min:0|max:100',
            'min_score' => 'nullable|integer|min:0|max:600',
            'deskripsi' => 'nullable|string',
            'plafon_min' => 'nullable|integer|min:0',
            'plafon_max' => 'nullable|integer|min:0',
            'tenor_min' => 'nullable|integer|min:1',
            'tenor_max' => 'nullable|integer|min:1',
            'bunga_persen' => 'nullable|numeric|min:0|max:100',
            'is_promoted' => 'nullable|boolean',
            'promo_image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'syarat' => 'nullable|array',
            'syarat.*' => 'string|max:255',
        ];

        if ($isUpdate) {
            $rules = collect($rules)->map(fn($rule) => str_starts_with($rule, 'nullable') ? $rule : "sometimes|$rule")->all();
        }

        return $request->validate($rules);
    }

    private function resolveCategoryFields(array $validated, Request $request): array
    {
        if ($request->has('category_ids')) {
            $ids = $request->input('category_ids', []);
            if (!is_array($ids)) {
                $ids = [];
            }
            if (!empty($ids)) {
                $firstCategory = BankCategory::find($ids[0]);
                if ($firstCategory) {
                    $validated['category_id'] = $firstCategory->id;
                    $validated['category'] = $firstCategory->name;
                }
            } else {
                $validated['category_id'] = null;
                $validated['category'] = null;
            }
            return $validated;
        }

        if (!empty($validated['category_id'])) {
            $category = BankCategory::find($validated['category_id']);
            if ($category) {
                $validated['category'] = $category->name;
            }
            return $validated;
        }

        $name = strtolower(trim($validated['category'] ?? 'terdaftar'));
        $category = BankCategory::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'sort_order' => 99]
        );
        $validated['category_id'] = $category->id;
        $validated['category'] = $category->name;
        return $validated;
    }

    private function buildDocPayload(string $type, string $path, BusinessProfile $profile): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return [
            'type' => $type,
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'extension' => $ext,
            'status' => $profile->getDocStatus($type),
            'feedback' => $profile->getDocFeedback($type),
        ];
    }

    public function auditDocument(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'document_type' => 'required|string',
            'status'        => 'required|string|in:pending,approved,rejected',
            'feedback'      => 'nullable|string|max:1000',
        ]);

        $profile = BusinessProfile::firstOrCreate(['user_id' => $validated['user_id']]);

        // Update status array
        $statuses = $profile->document_statuses ?? [];
        $statuses[$validated['document_type']] = $validated['status'];
        $profile->document_statuses = $statuses;

        // Update feedback array
        $feedbacks = $profile->document_feedbacks ?? [];
        if ($validated['status'] === 'rejected') {
            $feedbacks[$validated['document_type']] = $validated['feedback'] ?? 'Dokumen tidak sesuai';
        } else {
            unset($feedbacks[$validated['document_type']]);
        }
        $profile->document_feedbacks = $feedbacks;

        // Recalculate scores because document status might affect skor_legalitas, etc.
        $profile->recalculateScores();
        $profile->save();

        // Create a system notification for the user
        try {
            $docLabel = strtoupper(str_replace(['_path', '_upload'], '', $validated['document_type']));
            $statusText = $validated['status'] === 'approved' ? 'DISETUJUI' : ($validated['status'] === 'rejected' ? 'DITOLAK' : 'DITINJAU');
            $msg = "Dokumen {$docLabel} Anda telah {$statusText} oleh Admin.";
            if ($validated['status'] === 'rejected' && !empty($validated['feedback'])) {
                $msg .= " Catatan: " . $validated['feedback'];
            }

            \App\Models\Notification::create([
                'user_id' => $validated['user_id'],
                'title' => "Audit Berkas: {$docLabel}",
                'subject' => 'audit',
                'message' => $msg,
            ]);
        } catch (\Exception $e) {
            // ignore notification fail
        }

        return response()->json([
            'message' => 'Audit dokumen berhasil diperbarui',
            'scores' => [
                'skor_legalitas' => $profile->skor_legalitas,
                'skor_profitabilitas' => $profile->skor_profitabilitas,
                'skor_kolektibilitas' => $profile->skor_kolektibilitas,
                'skor_keberlanjutan' => $profile->skor_keberlanjutan,
                'skor_total' => $profile->skor_total,
            ]
        ]);
    }

    private function transformAd(Ad $ad): array
    {
        $arr = $ad->toArray();
        $arr['image_url'] = $this->resolveAdImageUrl($ad->image_url);
        return $arr;
    }

    private function resolveAdImageUrl(?string $value): ?string
    {
        if (empty($value)) return null;
        return $this->isAbsoluteUrl($value) ? $value : Storage::disk('public')->url($value);
    }

    private function transformArticle(Article $article): array
    {
        $arr = $article->toArray();
        $arr['image_url'] = $this->resolveArticleImageUrl($article->image_url);
        return $arr;
    }

    private function resolveArticleImageUrl(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        return $this->isAbsoluteUrl($value) ? $value : Storage::disk('public')->url($value);
    }

    private function isAbsoluteUrl(string $value): bool
    {
        return Str::startsWith($value, ['http://', 'https://']);
    }
}
