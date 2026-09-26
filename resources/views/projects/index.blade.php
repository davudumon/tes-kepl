<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Projects</title></head>
<body>
    <h1>Project List</h1>
    <p><a href="{{ route('projects.create') }}">Create project</a></p>
    @if (session('success')) <p>{{ session('success') }}</p> @endif
    <ul>
        @forelse ($projects as $project)
            <li>
                <strong>{{ $project->name }}</strong> ({{ $project->status }}) — {{ $project->description }}
                <form action="{{ route('projects.destroy', $project) }}" method="post" style="display:inline">
                    @csrf @method('DELETE') <button type="submit">Delete</button>
                </form>
            </li>
        @empty
            <li>No projects yet.</li>
        @endforelse
    </ul>
</body>
</html>
