<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Create Project</title></head>
<body>
    <h1>Create Project</h1>
    @if ($errors->any()) <p>{{ $errors->first() }}</p> @endif
    <form action="{{ route('projects.store') }}" method="post">
        @csrf
        <p><label>Name <input name="name" value="{{ old('name') }}"></label></p>
        <p><label>Description <textarea name="description">{{ old('description') }}</textarea></label></p>
        <p><label>Status <select name="status"><option value="planned">Planned</option><option value="active">Active</option><option value="done">Done</option></select></label></p>
        <button type="submit">Save</button>
    </form>
    <p><a href="{{ route('projects.index') }}">Back</a></p>
</body>
</html>
