@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    $unseenCounter = App\Models\ChMessage::where('to_id', Auth::user()->id)->where('seen', 0)->count();
@endphp

<header class="dash-header ai-topbar">
    <div class="header-wrapper ai-header-inner">
        <div class="ai-header-left">
            <button type="button" id="mobile-collapse" class="ai-menu-btn dash-head-link" aria-label="{{ __('Menu') }}">
                <i class="ti ti-menu-2"></i>
            </button>
        </div>

        <div class="ai-header-right">
            <ul class="list-unstyled mb-0 ai-header-actions">
                @if(\Auth::user()->type != 'client' && \Auth::user()->type != 'super admin')
                    <li class="dropdown dash-h-item drp-notification">
                        <a class="dash-head-link ai-icon-btn arrow-none" href="{{ url('chats') }}" aria-label="{{ __('Messages') }}">
                            <i class="ti ti-brand-hipchat"></i>
                            @if($unseenCounter > 0)
                                <span class="ai-badge-count">{{ $unseenCounter > 99 ? '99+' : $unseenCounter }}</span>
                            @endif
                        </a>
                    </li>
                @endif

                <li class="dropdown dash-h-item drp-company">
                    <a class="dash-head-link ai-user-btn dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="theme-avtar ai-user-avatar">
                            <img src="{{ !empty(\Auth::user()->avatar) ? $profile . \Auth::user()->avatar : $profile.'avatar.png' }}" alt="" class="img-fluid rounded-circle">
                        </span>
                        <span class="ai-user-meta hide-mob">
                            <span class="ai-user-greet">{{ __('Hi,') }}</span>
                            <span class="ai-user-name">{{ \Auth::user()->name }}</span>
                        </span>
                        <i class="ti ti-chevron-down ai-user-chevron hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end ai-user-dropdown">
                        <a href="{{ route('profile') }}" class="dropdown-item">
                            <i class="ti ti-user"></i>
                            <span>{{ __('Profile') }}</span>
                        </a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();" class="dropdown-item">
                            <i class="ti ti-power"></i>
                            <span>{{ __('Logout') }}</span>
                        </a>
                        <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
