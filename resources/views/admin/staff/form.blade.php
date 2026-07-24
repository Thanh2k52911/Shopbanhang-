@php($editing=isset($staff))
<div class="grid gap-5 rounded-xl border bg-white p-6 md:grid-cols-2">
<div><label class="mb-1 block text-sm font-medium">Họ tên</label><input class="w-full rounded-lg border-gray-300" name="name" value="{{ old('name',$staff->name ?? '') }}" required></div>
<div><label class="mb-1 block text-sm font-medium">Email</label><input class="w-full rounded-lg border-gray-300" type="email" name="email" value="{{ old('email',$staff->email ?? '') }}" required></div>
<div><label class="mb-1 block text-sm font-medium">Mật khẩu {{ $editing ? '(để trống nếu không đổi)' : '' }}</label><input class="w-full rounded-lg border-gray-300" type="password" name="password" {{ $editing?'':'required' }}></div>
<div><label class="mb-1 block text-sm font-medium">Xác nhận mật khẩu</label><input class="w-full rounded-lg border-gray-300" type="password" name="password_confirmation"></div>
<div><label class="mb-1 block text-sm font-medium">Trạng thái</label><select class="w-full rounded-lg border-gray-300" name="status">@foreach(['active'=>'Hoạt động','inactive'=>'Không hoạt động','blocked'=>'Đã khóa'] as $v=>$l)<option value="{{ $v }}" @selected(old('status',$staff->status ?? 'active')===$v)>{{ $l }}</option>@endforeach</select></div>
<div class="flex items-center gap-2 pt-7"><input type="checkbox" name="email_verified" value="1" @checked(old('email_verified',isset($staff) && $staff->email_verified_at))><span>Xác minh email</span></div>
<div class="md:col-span-2"><label class="mb-2 block text-sm font-medium">Vai trò</label><div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">@foreach($roles as $role)<label class="flex gap-2 rounded-lg border p-3"><input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id,old('role_ids',isset($staff)?$staff->roles->pluck('id')->all():[])))><span>{{ $role->display_name }}</span></label>@endforeach</div></div>
@if($errors->any())<div class="md:col-span-2 rounded-lg bg-red-50 p-3 text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="md:col-span-2 flex justify-end"><button class="rounded-lg bg-pink-600 px-5 py-2.5 text-white">{{ $editing?'Lưu thay đổi':'Tạo nhân viên' }}</button></div>
</div>
