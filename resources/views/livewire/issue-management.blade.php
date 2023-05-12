<div>
    <h2 class="mb-4 text-2xl font-bold">Add Issue</h2>
    <form
        class="mb-4"
        wire:submit.prevent="addIssue"
    >
        <div class="mb-4">
            <label
                class="block mb-2 font-bold text-gray-700"
                for="issueTitle"
            >
                Issue title
            </label>
            <input
                class="w-full px-4 py-2 border border-gray-400 rounded-lg"
                type="text"
                wire:model="issueTitle"
                placeholder="Issue title"
                required
            >
        </div>
        <div class="mb-4">
            <label
                class="block mb-2 font-bold text-gray-700"
                for="issueDescription"
            >
                Issue description (optional)
            </label>
            <textarea
                class="w-full px-4 py-2 border border-gray-400 rounded-lg"
                wire:model="issueDescription"
                placeholder="Issue description (optional)"
            ></textarea>
        </div>
        <button
            class="px-4 py-2 font-bold text-black transition-colors rounded bg-cyan-500 hover:bg-cyan-700 hover:text-white"
            type="submit"
        >Add Issue</button>
    </form>

    <h2 class="mb-4 text-2xl font-bold">Issues</h2>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-200">
                <th class="px-4 py-2 text-left">Title</th>
                <th class="px-4 py-2 text-left">Description</th>
                <th class="px-4 py-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($issues as $issue)
                <tr class="border-b border-gray-200">
                    <td class="px-4 py-2">{{ $issue->title }}</td>
                    <td class="px-4 py-2">{{ $issue->description }}</td>
                    <td>
                        <div class="flex flex-col gap-2 px-4 py-2 md:flex-row ">
                            <button
                                class="px-4 py-2 font-bold text-black rounded bg-cyan-500 hover:bg-cyan-700 hover:text-white"
                                wire:click="editIssue({{ $issue->id }})"
                            >Edit</button>
                            <button
                                class="px-4 py-2 font-bold text-white bg-red-500 rounded hover:bg-red-700"
                                wire:click="deleteIssue({{ $issue->id }})"
                            >Delete</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3">
                    <a
                        class="flex items-center justify-center py-2 mt-3 font-bold text-black transition-colors rounded bg-cyan-500 hover:bg-cyan-700 hover:text-white"
                        href="{{ route('session.voting', ['inviteCode' => $session->invite_code]) }}"
                    >Join Session</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
