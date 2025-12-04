@extends('buildora::layouts.buildora')

@section('content')
    <div class="max-w-6xl mx-auto" x-data="buildoraSettings()">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
                {{ __buildora('Buildora Settings') }}
            </h1>
            <p class="mt-1" style="color: var(--text-muted);">
                {{ __buildora('Manage your Buildora installation and system settings.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Actions --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Permissions Card --}}
                <div class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-semibold flex items-center gap-2" style="color: var(--text-primary);">
                                <i class="fas fa-shield-alt" style="color: var(--accent-color);"></i>
                                {{ __buildora('Permissions') }}
                            </h2>
                            <p class="mt-1 text-sm" style="color: var(--text-muted);">
                                {{ __buildora('Synchronize permissions for all Buildora resources.') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 text-sm font-medium rounded-full"
                              style="background: rgba(102, 126, 234, 0.1); color: var(--accent-color);">
                            <span x-text="stats.permission_count">{{ $stats['permission_count'] ?? 0 }}</span> {{ __buildora('permissions') }}
                        </span>
                    </div>

                    <div class="mt-4 p-4 rounded-xl" style="background: var(--bg-primary);">
                        <p class="text-sm" style="color: var(--text-secondary);">
                            {{ __buildora('This will scan all Buildora resources and create the necessary permissions (view, create, edit, delete) for each resource.') }}
                        </p>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <button type="button"
                                @click="syncPermissions()"
                                :disabled="loading.permissions"
                                class="inline-flex items-center gap-2 px-4 py-2.5 btn-primary text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50">
                            <i class="fas fa-sync-alt" :class="loading.permissions && 'animate-spin'"></i>
                            {{ __buildora('Sync Permissions') }}
                        </button>

                        <span x-show="messages.permissions"
                              x-transition
                              class="text-sm"
                              :class="messages.permissionsSuccess ? 'text-green-500' : 'text-red-500'"
                              x-text="messages.permissions">
                        </span>
                    </div>
                </div>

                {{-- Cache Card --}}
                <div class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-semibold flex items-center gap-2" style="color: var(--text-primary);">
                                <i class="fas fa-database" style="color: #f59e0b;"></i>
                                {{ __buildora('Cache Management') }}
                            </h2>
                            <p class="mt-1 text-sm" style="color: var(--text-muted);">
                                {{ __buildora('Clear application caches to refresh data.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 p-4 rounded-xl" style="background: var(--bg-primary);">
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <span style="color: var(--text-muted);">{{ __buildora('Cache Driver') }}</span>
                                <p class="font-medium" style="color: var(--text-primary);">{{ $stats['cache_driver'] ?? 'file' }}</p>
                            </div>
                            <div>
                                <span style="color: var(--text-muted);">{{ __buildora('Session Driver') }}</span>
                                <p class="font-medium" style="color: var(--text-primary);">{{ $stats['session_driver'] ?? 'file' }}</p>
                            </div>
                            <div>
                                <span style="color: var(--text-muted);">{{ __buildora('Queue Driver') }}</span>
                                <p class="font-medium" style="color: var(--text-primary);">{{ $stats['queue_driver'] ?? 'sync' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <button type="button"
                                @click="clearCache()"
                                :disabled="loading.cache"
                                class="inline-flex items-center gap-2 px-4 py-2.5 font-medium rounded-xl transition-all duration-200 disabled:opacity-50"
                                style="background: #f59e0b; color: white;">
                            <i class="fas fa-broom" :class="loading.cache && 'animate-spin'"></i>
                            {{ __buildora('Clear Cache') }}
                        </button>

                        <span x-show="messages.cache"
                              x-transition
                              class="text-sm"
                              :class="messages.cacheSuccess ? 'text-green-500' : 'text-red-500'"
                              x-text="messages.cache">
                        </span>
                    </div>
                </div>

                {{-- Resources Card --}}
                <div class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                    <h2 class="text-lg font-semibold flex items-center gap-2" style="color: var(--text-primary);">
                        <i class="fas fa-cubes" style="color: #10b981;"></i>
                        {{ __buildora('Resources') }}
                    </h2>
                    <p class="mt-1 text-sm" style="color: var(--text-muted);">
                        {{ __buildora('Overview of registered Buildora resources.') }}
                    </p>

                    <div class="mt-4 p-4 rounded-xl" style="background: var(--bg-primary);">
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">{{ __buildora('Total Resources') }}</span>
                            <span class="text-xl font-bold" style="color: var(--text-primary);">{{ $stats['resource_count'] ?? 0 }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm" style="color: var(--text-muted);">
                            {{ __buildora('Generate a new resource using:') }}
                        </p>
                        <code class="mt-2 block p-3 rounded-lg text-sm font-mono"
                              style="background: var(--bg-primary); color: var(--accent-color);">
                            php artisan buildora:resource ModelName
                        </code>
                    </div>
                </div>
            </div>

            {{-- Right Column: System Info --}}
            <div class="space-y-6">
                {{-- System Information --}}
                <div class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                    <h2 class="text-lg font-semibold flex items-center gap-2 mb-4" style="color: var(--text-primary);">
                        <i class="fas fa-info-circle" style="color: var(--accent-color);"></i>
                        {{ __buildora('System Information') }}
                    </h2>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid var(--border-color);">
                            <span class="text-sm" style="color: var(--text-muted);">{{ __buildora('PHP Version') }}</span>
                            <span class="text-sm font-medium" style="color: var(--text-primary);">{{ $stats['php_version'] ?? PHP_VERSION }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid var(--border-color);">
                            <span class="text-sm" style="color: var(--text-muted);">{{ __buildora('Laravel Version') }}</span>
                            <span class="text-sm font-medium" style="color: var(--text-primary);">{{ $stats['laravel_version'] ?? app()->version() }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid var(--border-color);">
                            <span class="text-sm" style="color: var(--text-muted);">{{ __buildora('Buildora Version') }}</span>
                            <span class="text-sm font-medium" style="color: var(--accent-color);">{{ $stats['buildora_version'] ?? config('buildora.version', 'dev') }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid var(--border-color);">
                            <span class="text-sm" style="color: var(--text-muted);">{{ __buildora('Environment') }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                  style="background: {{ ($stats['environment'] ?? app()->environment()) === 'production' ? '#10b981' : '#f59e0b' }}20; color: {{ ($stats['environment'] ?? app()->environment()) === 'production' ? '#10b981' : '#f59e0b' }};">
                                {{ $stats['environment'] ?? app()->environment() }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm" style="color: var(--text-muted);">{{ __buildora('Debug Mode') }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                  style="background: {{ ($stats['debug_mode'] ?? config('app.debug')) ? '#ef4444' : '#10b981' }}20; color: {{ ($stats['debug_mode'] ?? config('app.debug')) ? '#ef4444' : '#10b981' }};">
                                {{ ($stats['debug_mode'] ?? config('app.debug')) ? 'ON' : 'OFF' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                    <h2 class="text-lg font-semibold flex items-center gap-2 mb-4" style="color: var(--text-primary);">
                        <i class="fas fa-chart-bar" style="color: #10b981;"></i>
                        {{ __buildora('Quick Stats') }}
                    </h2>

                    <div class="space-y-4">
                        <div class="p-4 rounded-xl" style="background: var(--bg-primary);">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(102, 126, 234, 0.1);">
                                    <i class="fas fa-users" style="color: var(--accent-color);"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['user_count'] ?? 0 }}</p>
                                    <p class="text-xs" style="color: var(--text-muted);">{{ __buildora('Users') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl" style="background: var(--bg-primary);">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(16, 185, 129, 0.1);">
                                    <i class="fas fa-cubes" style="color: #10b981;"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['resource_count'] ?? 0 }}</p>
                                    <p class="text-xs" style="color: var(--text-muted);">{{ __buildora('Resources') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl" style="background: var(--bg-primary);">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(245, 158, 11, 0.1);">
                                    <i class="fas fa-shield-alt" style="color: #f59e0b;"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold" style="color: var(--text-primary);" x-text="stats.permission_count">{{ $stats['permission_count'] ?? 0 }}</p>
                                    <p class="text-xs" style="color: var(--text-muted);">{{ __buildora('Permissions') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documentation Link --}}
                <div class="rounded-2xl p-6" style="background: linear-gradient(135deg, var(--accent-color), #818cf8);">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2 mb-2">
                        <i class="fas fa-book"></i>
                        {{ __buildora('Documentation') }}
                    </h2>
                    <p class="text-sm text-white/80 mb-4">
                        {{ __buildora('Learn how to use Buildora effectively.') }}
                    </p>
                    <a href="https://buildora.dev/docs" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-external-link-alt"></i>
                        {{ __buildora('View Documentation') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function buildoraSettings() {
        return {
            stats: {
                permission_count: {{ $stats['permission_count'] ?? 0 }}
            },
            loading: {
                permissions: false,
                cache: false
            },
            messages: {
                permissions: '',
                permissionsSuccess: false,
                cache: '',
                cacheSuccess: false
            },

            async syncPermissions() {
                this.loading.permissions = true;
                this.messages.permissions = '';

                try {
                    const response = await fetch('{{ route("buildora.settings.sync-permissions") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.messages.permissions = data.message;
                        this.messages.permissionsSuccess = true;
                        this.stats.permission_count = data.permission_count;
                    } else {
                        this.messages.permissions = data.message || 'An error occurred';
                        this.messages.permissionsSuccess = false;
                    }
                } catch (error) {
                    this.messages.permissions = error.message;
                    this.messages.permissionsSuccess = false;
                } finally {
                    this.loading.permissions = false;
                    setTimeout(() => {
                        this.messages.permissions = '';
                    }, 5000);
                }
            },

            async clearCache() {
                this.loading.cache = true;
                this.messages.cache = '';

                try {
                    const response = await fetch('{{ route("buildora.settings.clear-cache") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.messages.cache = data.message;
                        this.messages.cacheSuccess = true;
                    } else {
                        this.messages.cache = data.message || 'An error occurred';
                        this.messages.cacheSuccess = false;
                    }
                } catch (error) {
                    this.messages.cache = error.message;
                    this.messages.cacheSuccess = false;
                } finally {
                    this.loading.cache = false;
                    setTimeout(() => {
                        this.messages.cache = '';
                    }, 5000);
                }
            }
        };
    }
    </script>
@endsection
