<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->hasPermission('roles.create'); }
    protected function prepareForValidation(): void { $this->merge(['name'=>str($this->input('name'))->trim()->lower()->replace(' ','_')->value()]); }
    public function rules(): array { return ['name'=>['required','alpha_dash','max:50','unique:roles,name'],'display_name'=>['required','string','max:100'],'description'=>['nullable','string','max:2000'],'permission_ids'=>['nullable','array'],'permission_ids.*'=>['integer','exists:permissions,id']]; }
}
