<!-- resources/views/livewire/student-form.blade.php -->
<div>
    <input wire:model="firstname" type="text" placeholder="First Name" class="form-control">
    <input wire:model="middlename" type="text" placeholder="Middle Name" class="form-control">
    <input wire:model="lastname" type="text" placeholder="Last Name" class="form-control">

    <input wire:model="name" type="text" placeholder="Full Name" class="form-control" readonly>
</div>
