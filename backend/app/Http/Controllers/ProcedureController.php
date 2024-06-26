<?php

namespace App\Http\Controllers;

use App\Models\Procedures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\QueryException;
use mikehaertl\pdftk\Pdf;

class ProcedureController extends Controller
{
    public function getProcedure()
    {
        $procedures = Procedures::where('is_active', true)
            ->where('file_path', 'LIKE', '%.pdf')
            ->select('id', 'document_type', 'department_id', 'document_name', 'file_path', 'created_at')
            ->get();

        return response()->json($procedures);
    }

    public function uploadProcedure(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'file' => 'nullable|file|mimes:pdf|max:10240', // Only allow PDF files
                'document_name' => 'required|string',
                'document_type' => 'required|string',
                'department_id' => 'required|numeric|exists:departments,id',
            ]);

            $url = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                if (!$file->isValid()) {
                    return response()->json(['error' => 'The uploaded file is invalid.'], 422);
                }

                $originalFileName = $file->getClientOriginalName();
                $filename = $originalFileName;

                $path = 'uploads/procedure/document_type/' . $request->input('document_type') . '/' . $originalFileName;
                $path = $file->storeAs($path, $filename, 'public');
                $url = URL::to('/') . '/storage/' . $path;

                $password = 'bma@2024';

                try {
                    $pdf = new Pdf(public_path('storage/' . $path));

                    // Set both user and owner password for encryption
                    $pdf->setPassword($password, $password);

                    $pdf->saveAs(public_path('storage/' . $path));
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Error setting password: ' . $e->getMessage()], 500);
                }
            }

            $procedure = new Procedures();
            $procedure->document_name = $request->input('document_name');
            $procedure->file_path = $url;
            $procedure->document_type = $request->input('document_type');
            $procedure->department_id = $request->input('department_id');
            $procedure->is_active = true;
            $procedure->save();

            return response()->json(['message' => 'Procedure uploaded successfully'], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}
