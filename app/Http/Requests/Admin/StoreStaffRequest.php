<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->hasPermission('users.create'); }
    public function rules(): array { return [
        'name'=>['required','string','max:255'], 'email'=>['required','email','max:255','unique:users,email'],
        'password'=>['required','string','min:8','confirmed'], 'status'=>['required',Rule::in(['active','inactive','blocked'])],
        'email_verified'=>['nullable','boolean'], 'role_ids'=>['required','array','min:1'],
        'role_ids.*'=>['integer',Rule::exists('roles','id')->where(fn($q)=>$q->where('name','!=','customer'))],
    ]; }
}
