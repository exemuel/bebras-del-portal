<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    /**
     * Search certificates by Bebras username (GET — bookmarkable URL).
     */
    public function search(Request $request): View
    {
        $searched           = false;
        $username           = null;
        $certificatesByYear = collect();
        $totalCount         = 0;
        $yearCount          = 0;
        $filterYear         = null;
        $certificateYears   = Certificate::availableYears();

        if ($request->filled('username')) {
            $validated = $request->validate([
                'username' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-Z0-9_\-\.\@]+$/'],
                'year'     => ['nullable', 'integer', 'min:2000', 'max:2099'],
            ], [
                'username.required' => 'Mohon masukkan Bebras Username Anda.',
                'username.regex'    => 'Format username mengandung karakter tidak valid.',
            ]);

            $searched   = true;
            $username   = trim($validated['username']);
            $normalized = Certificate::normalizeUsername($username);
            $filterYear = $validated['year'] ?? null;

            $query = Certificate::where('bebras_username', $normalized)
                ->orderBy('year', 'desc')
                ->orderBy('award_type', 'asc');

            if ($filterYear) {
                $query->where('year', $filterYear);
            }

            $all                = $query->get();
            $totalCount         = $all->count();
            $certificatesByYear = $all->groupBy('year');
            $yearCount          = $certificatesByYear->count();
        }

        return view('public.certificates.search', compact(
            'searched', 'username', 'certificatesByYear',
            'totalCount', 'yearCount', 'filterYear', 'certificateYears'
        ));
    }


    /**
     * Secure certificate PDF download.
     */
    public function download(Certificate $certificate): StreamedResponse
    {
        if (!Storage::disk('local')->exists($certificate->file_path)) {
            abort(404, 'Berkas sertifikat tidak ditemukan di server.');
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return Storage::disk('local')->download(
            $certificate->file_path,
            $certificate->download_filename,
            $headers
        );
    }
}
