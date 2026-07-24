<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->hasPermission('users.update'); }
    public function rules(): array { $staff=$this->route('staff'); return [
        'name'=>['required','string','max:255'], 'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($staff?->id)],
        'password'=>['nullable','string','min:8','confirmed'], 'status'=>['required',Rule::in(['active','inactive','blocked'])],
        'blocked_reason'=>['nullable','string','max:2000'], 'email_verified'=>['nullable','boolean'],
        'role_ids'=>['required','array','min:1'], 'role_ids.*'=>['integer',Rule::exists('roles','id')->where(fn($q)=>$q->where('name','!=','customer'))],
    ]; }
}
