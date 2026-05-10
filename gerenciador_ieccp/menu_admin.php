<nav class="menu-admin">
    <div class="menu-left">
        <span class="brand">⛪ IECCP</span>
        <div class="nav-links">
            <a href="painel" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'painel.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-newspaper"></i> Notícias
            </a>
            <a href="painel_pastoral" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'painel_pastoral.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-cross"></i> Pastoral
            </a>
            <a href="painel_agenda" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'painel_agenda.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i> Agenda
            </a>
            <a href="painel_destaques" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'painel_destaques.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-film"></i> Destaques
            </a>
        </div>
    </div>
    <div class="menu-right">
        <span class="menu-user"><i class="fa-solid fa-circle-user"></i> Admin</span>
        <a href="logout" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
    </div>
</nav>

<style>
    .menu-admin {
        background: #2c3e50;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .menu-left {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    .brand {
        color: #fff;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: -0.01em;
        white-space: nowrap;
        padding-right: 1.25rem;
        border-right: 1px solid rgba(255,255,255,0.1);
    }

    .nav-links {
        display: flex;
        gap: 2px;
        flex-wrap: wrap;
    }

    .nav-link {
        color: rgba(255,255,255,0.6);
        text-decoration: none;
        padding: 0.45rem 0.9rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.15s, color 0.15s;
        white-space: nowrap;
    }

    .nav-link:hover {
        background: rgba(255,255,255,0.08);
        color: #fff;
    }

    .nav-link.active {
        background: #27ae60;
        color: #fff;
    }

    .menu-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .menu-user {
        color: rgba(255,255,255,0.4);
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-logout {
        color: #e74c3c;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        border: 1px solid rgba(231,76,60,0.4);
        padding: 0.4rem 0.9rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.15s, color 0.15s;
        white-space: nowrap;
    }

    .btn-logout:hover {
        background: #e74c3c;
        color: #fff;
        border-color: #e74c3c;
    }

    @media (max-width: 640px) {
        .menu-admin {
            border-radius: 8px;
            padding: 0.65rem 1rem;
        }
        .brand { font-size: 0.9rem; padding-right: 0.9rem; }
        .nav-link { padding: 0.4rem 0.65rem; font-size: 0.8rem; }
        .nav-link span.label { display: none; }
        .menu-user { display: none; }
        .btn-logout span { display: none; }
    }
</style>
