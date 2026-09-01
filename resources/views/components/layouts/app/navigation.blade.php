@can('view_dashboard')
    <x-nav.link route="dashboard" icon="home">{{ __('admin.Dashboard') }}</x-nav.link>
@endcan

@if(can('view_system_settings') || can('view_roles') || can('view_audit_trails'))
    <x-nav.divider>{{ __('admin.Settings') }}</x-nav.divider>
@endif

@can('view_audit_trails')
    <x-nav.link route="admin.settings.audit-trails.index" icon="identification">{{ __('admin.Audit Trails') }}</x-nav.link>
@endcan

@can('view_roles')
    <x-nav.link route="admin.settings.roles.index" icon="archive-box">{{ __('admin.Roles') }}</x-nav.link>
@endcan

@can('view_system_settings')
    <x-nav.link route="admin.settings" icon="wrench-screwdriver">{{ __('admin.System Settings') }}</x-nav.link>
@endcan

@can('view_system_settings')
    <x-nav.link route="admin.settings.ai-assistant" icon="cpu-chip">{{ __('AI Assistant') }}</x-nav.link>
@endcan

@can('view_system_settings')
    <x-nav.link route="admin.settings.languages.index" icon="language">{{ __('admin.Languages') }}</x-nav.link>
@endcan

@can('view_system_settings')
    <x-nav.link route="admin.settings.notifications" icon="bell">{{ __('admin.Notifications') }}</x-nav.link>
@endcan

<x-nav.divider>{{ __('admin.Modules') }}</x-nav.divider>

























































































<x-nav.divider>{{ __('admin.Account') }}</x-nav.divider>


@can('view_barbers')
    <x-nav.link route="admin.barber-dashboard" icon="chart-bar">{{ __('Dashboard') }}</x-nav.link>
    <x-nav.link route="admin.barbers.index" icon="user-group">{{ __('Barbers') }}</x-nav.link>
    <x-nav.link route="admin.bookings.index" icon="calendar">{{ __('Bookings') }}</x-nav.link>
    <x-nav.link route="admin.customers.index" icon="users">{{ __('Customers') }}</x-nav.link>
    <x-nav.link route="admin.services.index" icon="queue-list">{{ __('Services') }}</x-nav.link>
    <x-nav.link route="admin.payments.index" icon="banknotes">{{ __('Payments') }}</x-nav.link>
    <x-nav.link route="admin.reminders.index" icon="bell">{{ __('Reminders') }}</x-nav.link>
    <x-nav.link route="admin.sms.index" icon="chat-bubble-bottom-center-text">{{ __('SMS Settings') }}</x-nav.link>
@endcan

@can('view_users')
    <x-nav.link route="admin.users.index" icon="users">{{ __('admin.Users') }}</x-nav.link>
@endcan

