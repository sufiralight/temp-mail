<x-ui.page title="Install Temp Mail SaaS">
    <main>
        <x-ui.container size="md" class="py-8 sm:py-12">
            <x-ui.stack gap="lg">
                <x-ui.page-header :title="[1 => 'Database Configuration', 'Administrator Account', 'Installation Complete'][$step]"
                    :description="'Step '.$step.' of 3'">
                    <x-slot:actions><x-ui.appearance-select id="install-appearance" /></x-slot:actions>
                </x-ui.page-header>

                @foreach (['connection', 'installation', 'engine', 'password_confirmation'] as $key)
                    @if ($errors->has($key))
                        <x-ui.alert variant="error">{{ $errors->first($key) }}</x-ui.alert>
                    @endif
                @endforeach
                @if (session('notice') && $step === 1)
                    <x-ui.alert variant="success">{{ session('notice') }}</x-ui.alert>
                @endif

                <x-ui.card>
                    @if ($step === 1)
                        <form method="post" action="{{ route('installation.test', absolute: false) }}">
                            @csrf
                            <x-ui.stack>
                                <x-ui.select name="engine" label="Database engine" required>
                                    <option value="mysql" @selected(($fields['engine'] ?? '') === 'mysql')>MySQL</option>
                                    <option value="mariadb" @selected(($fields['engine'] ?? '') === 'mariadb')>MariaDB</option>
                                </x-ui.select>
                                <x-ui.grid gap="md">
                                    <x-ui.input name="host" label="Database host" :value="$fields['host'] ?? '127.0.0.1'" required />
                                    <x-ui.input name="port" label="Database port" type="number" min="1" max="65535" :value="$fields['port'] ?? 3306" required />
                                </x-ui.grid>
                                <x-ui.input name="database" label="Database name" :value="$fields['database'] ?? ''" required />
                                <x-ui.input name="username" label="Database username" :value="$fields['username'] ?? ''" required />
                                <x-ui.password-field name="database_password" label="Database password" autocomplete="off" />
                                <x-ui.button type="submit" variant="secondary" leading-icon="search">Test Connection</x-ui.button>
                            </x-ui.stack>
                        </form>
                        <x-slot:footer>
                            <form method="post" action="{{ route('installation.next', absolute: false) }}">
                                @csrf
                                <x-ui.button type="submit" trailing-icon="arrow-right" :disabled="! $verified">Continue</x-ui.button>
                            </form>
                        </x-slot:footer>
                    @elseif ($step === 2)
                        <form method="post" data-install-form action="{{ route('installation.commit', absolute: false) }}">
                            @csrf
                            <x-ui.stack>
                                <x-ui.input name="name" label="Full name" autocomplete="name" required />
                                <x-ui.input name="email" label="Email address" type="email" autocomplete="email" required />
                                <x-ui.password-field name="password" label="Password" description="Use at least 14 characters and at most 72 bytes." autocomplete="new-password" required />
                                <x-ui.password-field name="password_confirmation" label="Confirm password" autocomplete="new-password" required />
                                <x-ui.button type="submit" leading-icon="check"><x-ui.spinner size="sm" data-install-spinner hidden /> Install Application</x-ui.button>
                            </x-ui.stack>
                        </form>
                        <x-slot:footer>
                            <form method="post" action="{{ route('installation.next', absolute: false) }}">
                                @csrf
                                <x-ui.button type="submit" name="direction" value="back" variant="secondary">Back</x-ui.button>
                            </form>
                        </x-slot:footer>
                    @else
                        <x-ui.alert variant="success" title="Setup complete">
                            The database schema is ready and the first Super Admin account was created.
                        </x-ui.alert>
                        <x-slot:footer>
                            <a class="ui-button" data-variant="primary" href="{{ route('login', absolute: false) }}">Continue to Login</a>
                        </x-slot:footer>
                    @endif
                </x-ui.card>
            </x-ui.stack>
        </x-ui.container>
    </main>
</x-ui.page>
