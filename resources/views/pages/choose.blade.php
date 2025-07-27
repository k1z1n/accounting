@extends('template.app')
@section('title', 'Выбор раздела')
@section('content')
<style>
    body {
        background: linear-gradient(135deg, #232b3a 0%, #0f172a 100%) !important;
    }
    .choose-block {
        background: #181f2a;
        border-radius: 16px;
        border: 1.5px solid #232b3a;
        max-width: 420px;
        width: 100%;
        padding: 2.5rem 2rem;
        margin: 0 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        animation: fadeInUp 0.7s cubic-bezier(.23,1.01,.32,1) both;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(40px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .choose-title {
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 2.2rem;
        text-align: center;
        letter-spacing: -0.5px;
    }
    .choose-grid {
        display: flex;
        gap: 1.2rem;
        width: 100%;
        justify-content: center;
    }
    .choose-card {
        aspect-ratio: 1 / 1;
        width: 140px;
        min-width: 0;
        background: #232b3a;
        border-radius: 12px;
        border: 1.5px solid #232b3a;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.16s, color 0.16s;
        position: relative;
        outline: none;
        margin-bottom: 0;
        padding: 0;
        box-shadow: none;
    }
    .choose-card:hover:not(:disabled), .choose-card:focus-visible:not(:disabled) {
        background: #1e293b;
        color: #38bdf8;
    }
    .choose-card:disabled {
        cursor: not-allowed;
        opacity: 0.6;
        background: #232b3a;
        color: #94a3b8;
    }
    .choose-lock {
        position: absolute;
        top: -16px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: default;
    }
    .choose-lock-bg {
        background: rgba(30,41,59,0.92);
        border-radius: 50%;
        border: 1.5px solid #fff;
        box-shadow: 0 2px 8px #38bdf855;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
    }
    .choose-lock svg {
        width: 16px;
        height: 16px;
        color: #fff;
        filter: drop-shadow(0 0 2px #38bdf8cc);
        display: block;
    }
    .choose-card .tooltip {
        display: none;
        position: absolute;
        top: 38px;
        left: 50%;
        transform: translateX(-50%);
        background: #232b3a;
        color: #fff;
        padding: 0.4em 1em;
        border-radius: 0.6em;
        font-size: 1rem;
        white-space: nowrap;
        box-shadow: 0 2px 12px 0 #232b3a99;
        z-index: 10;
    }
    .choose-card:disabled:hover .tooltip,
    .choose-lock:hover + .tooltip {
        display: block;
    }
    .choose-card svg {
        width: 2.2em;
        height: 2.2em;
        margin-bottom: 0.5em;
    }
    .choose-card span {
        color: #e5e7eb;
        font-weight: 600;
        margin-top: 0.2em;
    }
    .choose-card:disabled span {
        color: #b6c2d1;
    }
    .choose-card {
        position: relative;
        overflow: hidden;
    }
    .choose-card-disabled-overlay {
        position: absolute;
        inset: 0;
        background: rgba(30,41,59,0.72);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        transition: background 0.2s;
        pointer-events: none;
    }
    .choose-card-disabled-overlay .choose-lock-center {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: auto;
    }
    .choose-lock-center svg {
        width: 38px;
        height: 38px;
        color: #fff;
        filter: drop-shadow(0 2px 8px #232b3a88);
        opacity: 0.92;
    }
    .choose-lock-center:hover + .tooltip,
    .choose-card:disabled:hover .tooltip {
        display: block;
    }
    .choose-card .tooltip {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, 60px);
        background: #232b3a;
        color: #fff;
        padding: 0.4em 1em;
        border-radius: 0.6em;
        font-size: 1rem;
        white-space: nowrap;
        box-shadow: 0 2px 12px 0 #232b3a99;
        z-index: 10;
    }
    @media (max-width: 600px) {
        .choose-block {
            padding: 1.2rem 0.5rem;
            max-width: 98vw;
        }
        .choose-title { font-size: 1.2rem !important; margin-bottom: 1.2rem; }
        .choose-grid {
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }
        .choose-card {
            width: 100px;
            font-size: 1rem;
        }
        .choose-card svg { width: 1.5em !important; height: 1.5em !important; }
    }
</style>
<div class="flex items-center justify-center min-h-[90vh] w-full">
    <div class="choose-block">
        <div class="choose-title">Выберите раздел</div>
        <div class="choose-grid">
            <form method="POST" action="{{ route('choose.section') }}">
                @csrf
                <input type="hidden" name="section" value="applications">
                <button type="submit" class="choose-card @if(auth()->user()->role !== 'admin') opacity-70 @endif" @if(auth()->user()->role !== 'admin') disabled @endif tabindex="0">
                    @if(auth()->user()->role !== 'admin')
                        <div class="choose-card-disabled-overlay">
                            <span class="choose-lock-center" tabindex="-1">
                                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <rect x="7" y="11" width="10" height="7" rx="2" stroke="currentColor"/>
                                    <path d="M12 15v2" stroke="currentColor" stroke-linecap="round"/>
                                    <path d="M9 11V9a3 3 0 016 0v2" stroke="currentColor"/>
                                </svg>
                            </span>
                            <span class="tooltip">Только для администраторов</span>
                        </div>
                    @endif
                    <svg class="text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none"/><path d="M8 12h8M8 16h8M8 8h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Заявки</span>
                </button>
            </form>
            <form method="POST" action="{{ route('choose.section') }}">
                @csrf
                <input type="hidden" name="section" value="dashboard">
                <button type="submit" class="choose-card @if(auth()->user()->role !== 'admin') opacity-70 @endif" @if(auth()->user()->role !== 'admin') disabled @endif tabindex="0">
                    @if(auth()->user()->role !== 'admin')
                        <div class="choose-card-disabled-overlay">
                            <span class="choose-lock-center" tabindex="-1">
                                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <rect x="7" y="11" width="10" height="7" rx="2" stroke="currentColor"/>
                                    <path d="M12 15v2" stroke="currentColor" stroke-linecap="round"/>
                                    <path d="M9 11V9a3 3 0 016 0v2" stroke="currentColor"/>
                                </svg>
                            </span>
                            <span class="tooltip">Только для администраторов</span>
                        </div>
                    @endif
                    <svg class="text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm4 0h2v-2H7v2zm4 0h2v-2h-2v2zm4 0h2v-2h-2v2zm4 0h2v-2h-2v2z"/><rect x="3" y="5" width="18" height="14" rx="3" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                    <span>Статистика</span>
                </button>
            </form>

        </div>
        @if(session('error'))
            <div class="mt-6 flex items-center gap-2 text-red-400 font-semibold text-center animate-pulse text-base">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>
</div>
@endsection
