<div class="grid grid-cols-5">
    @foreach ($users as $user)
        <div class="flex flex-col">
            <div>{{ $user->name }}</div>
            <div>{{ $votes->where('user_id', $user->id)->first()->value }}</div>
        </div>
    @endforeach
</div>
