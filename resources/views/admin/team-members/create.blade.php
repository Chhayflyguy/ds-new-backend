<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Team Member - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Add Team Member</h1>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.team-members.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                            ← Back to List
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg font-medium border border-gray-300 hover:bg-gray-50">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form action="{{ route('admin.team-members.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Profile Image -->
                    <div class="mb-6">
                        <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-2">Profile Image</label>
                        <input type="file" name="profile_image" id="profile_image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('profile_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" id="description" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telegram Link -->
                    <div class="mb-6">
                        <label for="telegram_link" class="block text-sm font-medium text-gray-700 mb-2">Telegram Link</label>
                        <input type="url" name="telegram_link" id="telegram_link" value="{{ old('telegram_link') }}" placeholder="https://t.me/username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('telegram_link')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Facebook Link -->
                    <div class="mb-6">
                        <label for="facebook_link" class="block text-sm font-medium text-gray-700 mb-2">Facebook Link</label>
                        <input type="url" name="facebook_link" id="facebook_link" value="{{ old('facebook_link') }}" placeholder="https://facebook.com/username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('facebook_link')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-6">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" placeholder="+1234567890" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('admin.team-members.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Create Team Member
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

