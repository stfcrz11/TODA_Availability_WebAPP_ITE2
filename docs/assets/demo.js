// docs/assets/demo.js - Static (GitHub Pages) stand-in for the PHP/MySQL backend.
// Data lives in the browser's localStorage, mirroring the toda_system tables.
(function () {
    const DB_KEY = 'toda_demo_db_v1';
    const SESSION_KEY = 'toda_demo_session_v1';

    const STATUSES = {
        available: { label: 'Available', badge: 'success', icon: 'user-check', desc: 'Ready to accept passengers' },
        ontrip:    { label: 'On Trip',   badge: 'warning', icon: 'user-clock', desc: 'Currently on a trip' },
        offline:   { label: 'Offline',   badge: 'danger',  icon: 'user-slash', desc: 'Not taking passengers' }
    };

    // ---- storage (falls back to memory if localStorage is blocked) ----
    const memory = {};
    const store = {
        get(k) { try { return localStorage.getItem(k); } catch (e) { return k in memory ? memory[k] : null; } },
        set(k, v) { try { localStorage.setItem(k, v); } catch (e) { memory[k] = v; } },
        remove(k) { try { localStorage.removeItem(k); } catch (e) { delete memory[k]; } }
    };

    // MySQL-style "YYYY-MM-DD HH:MM:SS" in local time
    function sqlDate(d) {
        const p = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
    }
    function ago(minutes) { return sqlDate(new Date(Date.now() - minutes * 60000)); }
    function now() { return sqlDate(new Date()); }
    function parseSql(s) { return s ? new Date(s.replace(' ', 'T')) : null; }

    function seed() {
        const db = {
            users: [
                { userid: 1, username: 'admin', password: 'admin123', userrole: 'admin' },
                { userid: 2, username: 'juan',  password: 'driver123', userrole: 'driver' },
                { userid: 3, username: 'john',  password: 'driver123', userrole: 'driver' },
                { userid: 4, username: 'luis',  password: 'driver123', userrole: 'driver' },
                { userid: 5, username: 'maria', password: 'driver123', userrole: 'driver' }
            ],
            tricycle: [
                { tricycleid: 1, plate_no: 'TEST-001', color: 'Red' },
                { tricycleid: 2, plate_no: 'TEST-002', color: 'Red' },
                { tricycleid: 3, plate_no: 'TEST-003', color: 'Blue' },
                { tricycleid: 4, plate_no: 'TEST-004', color: 'Green' },
                { tricycleid: 5, plate_no: 'TEST-005', color: 'Yellow' }
            ],
            driver: [
                { driverid: 1, userid: 2, fullname: 'Juan Dela Cruz', contactno: '09384950293', address: 'Santa Rosa, Santiago City, Isabela', tricycleid: 2 },
                { driverid: 2, userid: 3, fullname: 'John Doe',       contactno: '09887665544', address: 'Santa Rosa, Santiago City, Isabela', tricycleid: 3 },
                { driverid: 3, userid: 4, fullname: 'Luis Jay',       contactno: '09832848747', address: 'Santa Rosa, Santiago City, Isabela', tricycleid: 4 },
                { driverid: 4, userid: 5, fullname: 'Maria Santos',   contactno: '09171234567', address: 'Victory Norte, Santiago City, Isabela', tricycleid: 5 }
            ],
            availability: [],
            driver_status_log: [],
            triplogs: [],
            seq: {}
        };

        // Build a believable history: a few completed trips, one ongoing
        const history = [
            [1, 'available', 300], [1, 'ontrip', 240], [1, 'available', 215], [1, 'ontrip', 90], [1, 'available', 70],
            [2, 'available', 200], [2, 'ontrip', 150], [2, 'available', 120], [2, 'ontrip', 12],
            [3, 'available', 400], [3, 'offline', 60],
            [4, 'available', 45]
        ];
        const tricycleOf = id => db.driver.find(d => d.driverid === id).tricycleid;
        history.forEach(([driverid, status, minutes]) => {
            const ts = ago(minutes);
            db.availability.push({ availabilityid: db.availability.length + 1, driverid, status, last_updated: ts });
            db.driver_status_log.push({ logid: db.driver_status_log.length + 1, driverid, status, updated_at: ts });
            if (status === 'ontrip') {
                db.triplogs.push({ triplogid: db.triplogs.length + 1, driverid, tricycleid: tricycleOf(driverid), trip_start: ts, trip_end: null });
            } else {
                const open = db.triplogs.find(t => t.driverid === driverid && !t.trip_end);
                if (open) open.trip_end = ts;
            }
        });
        // A couple of trips from yesterday so the log has more than one day
        const y = new Date(Date.now() - 86400000);
        y.setHours(8, 15, 0);
        const yEnd = new Date(y.getTime() + 25 * 60000);
        db.triplogs.unshift({ triplogid: 0, driverid: 3, tricycleid: 4, trip_start: sqlDate(y), trip_end: sqlDate(yEnd) });
        db.triplogs.forEach((t, i) => { t.triplogid = i + 1; });

        ['users', 'tricycle', 'driver', 'availability', 'driver_status_log', 'triplogs'].forEach(t => {
            db.seq[t] = db[t].length;
        });
        return db;
    }

    let db;
    try { db = JSON.parse(store.get(DB_KEY)); } catch (e) { db = null; }
    if (!db || !db.users) { db = seed(); save(); }

    function save() { store.set(DB_KEY, JSON.stringify(db)); }
    function nextId(table) { db.seq[table] = (db.seq[table] || 0) + 1; return db.seq[table]; }

    // ---- session ----
    function session() {
        try { return JSON.parse(store.get(SESSION_KEY)); } catch (e) { return null; }
    }

    function login(username, password) {
        const user = db.users.find(u => u.username === username.trim());
        if (!user || user.password !== password) return false;
        store.set(SESSION_KEY, JSON.stringify({ user_id: user.userid, username: user.username, role: user.userrole }));
        return true;
    }

    function logout() { store.remove(SESSION_KEY); }

    // Equivalent of checkLogin()/requireAdmin(); returns the session or redirects
    function requireLogin(role) {
        const s = session();
        if (!s || !db.users.some(u => u.userid === s.user_id)) {
            logout();
            location.replace('login.html');
            throw new Error('redirecting');
        }
        if (role && s.role !== role) {
            location.replace('dashboard.html');
            throw new Error('redirecting');
        }
        return s;
    }

    // ---- data helpers (mirror config.php) ----
    function getDriverByUser(userid) {
        return db.driver.filter(d => d.userid === userid).sort((a, b) => a.driverid - b.driverid)[0] || null;
    }

    function getDriverStatus(driverid) {
        let latest = null;
        db.availability.forEach(a => { if (a.driverid === driverid && (!latest || a.availabilityid > latest.availabilityid)) latest = a; });
        return latest ? latest.status : 'offline';
    }

    function setDriverStatus(driver, status) {
        const current = getDriverStatus(driver.driverid);
        if (current === status) return;
        const ts = now();
        db.availability.push({ availabilityid: nextId('availability'), driverid: driver.driverid, status, last_updated: ts });
        db.driver_status_log.push({ logid: nextId('driver_status_log'), driverid: driver.driverid, status, updated_at: ts });
        if (current === 'ontrip') {
            db.triplogs.forEach(t => { if (t.driverid === driver.driverid && !t.trip_end) t.trip_end = ts; });
        }
        if (status === 'ontrip') {
            db.triplogs.push({ triplogid: nextId('triplogs'), driverid: driver.driverid, tricycleid: driver.tricycleid, trip_start: ts, trip_end: null });
        }
        save();
    }

    function tricycle(id) { return db.tricycle.find(t => t.tricycleid === id) || null; }

    function availableDrivers() {
        return db.driver
            .map(d => ({ d, status: getDriverStatus(d.driverid) }))
            .filter(x => x.status === 'available')
            .map(({ d, status }) => {
                const t = tricycle(d.tricycleid);
                return { fullname: d.fullname, contactno: d.contactno, plate_no: t ? t.plate_no : null, status };
            })
            .sort((a, b) => a.fullname.localeCompare(b.fullname));
    }

    function addUser(f) {
        if (!f.username || !f.password) return 'Username and password are required.';
        if (f.password.length < 6) return 'Password must be at least 6 characters.';
        if (f.role !== 'admin' && f.role !== 'driver') return 'Invalid role.';
        if (f.role === 'driver' && (!f.fullname || !f.tricycleid)) return 'Drivers need a full name and an assigned tricycle.';
        if (db.users.some(u => u.username === f.username)) return 'Username already exists!';

        const userid = nextId('users');
        db.users.push({ userid, username: f.username, password: f.password, userrole: f.role });
        if (f.role === 'driver') {
            const driverid = nextId('driver');
            db.driver.push({ driverid, userid, fullname: f.fullname, contactno: f.contactno, address: f.address, tricycleid: f.tricycleid });
            db.availability.push({ availabilityid: nextId('availability'), driverid, status: 'offline', last_updated: now() });
        }
        save();
        return null;
    }

    function deleteUser(userid) {
        const driverIds = db.driver.filter(d => d.userid === userid).map(d => d.driverid);
        const notMine = row => !driverIds.includes(row.driverid);
        db.driver_status_log = db.driver_status_log.filter(notMine);
        db.availability = db.availability.filter(notMine);
        db.triplogs = db.triplogs.filter(notMine);
        db.driver = db.driver.filter(notMine);
        db.users = db.users.filter(u => u.userid !== userid);
        save();
    }

    function reset() {
        store.remove(DB_KEY);
        logout();
        location.href = 'index.html';
    }

    // ---- shared UI ----
    function h(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function fmtDate(s, withYear) {
        const d = parseSql(s);
        if (!d) return '—';
        return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: withYear === false ? undefined : 'numeric' });
    }

    function fmtTime(s) {
        const d = parseSql(s);
        return d ? d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '—';
    }

    function isToday(s) {
        const d = parseSql(s);
        return d && d.toDateString() === new Date().toDateString();
    }

    // Same markup as includes/sidebar.php
    function sidebarHtml(s, activePage) {
        const isAdmin = s.role === 'admin';
        let statusHtml = '';
        if (s.role === 'driver') {
            const driver = getDriverByUser(s.user_id);
            if (driver) {
                const st = STATUSES[getDriverStatus(driver.driverid)];
                statusHtml = `<div style="margin-top: 10px;">
                    <span style="font-size: 12px; color: #64748b;">Status: </span>
                    <span id="current-status" class="badge badge-${st.badge}">${st.label}</span>
                </div>`;
            }
        }
        const item = (href, icon, label, key) =>
            `<a href="${href}" class="nav-item ${activePage === key ? 'active' : ''}"><i class="fas fa-${icon}"></i><span>${label}</span></a>`;

        return `<nav class="sidebar">
            <div class="sidebar-header">
                <h1><i class="fas fa-taxi"></i><span>TODA System</span></h1>
                <div class="user-info">
                    <span><i class="fas fa-user"></i> ${h(s.username)}</span>
                    <span class="user-role"><i class="fas fa-${isAdmin ? 'crown' : 'taxi'}"></i> ${isAdmin ? 'Admin' : 'Driver'}</span>
                </div>
                ${statusHtml}
            </div>
            <div class="sidebar-nav">
                ${item('index.html', 'home', 'Passenger View', 'home')}
                ${item('dashboard.html', 'chart-line', 'Dashboard', 'dashboard')}
                ${isAdmin ? `
                    <div class="nav-divider"></div>
                    <div style="padding: 0 20px; margin: 20px 0;">
                        <span style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-crown"></i> Admin Panel</span>
                    </div>
                    ${item('user_management.html', 'user-tie', 'User Management', 'users')}
                    ${item('trip_logs.html', 'clipboard-list', 'Trip Logs', 'trips')}
                ` : `
                    <div class="nav-divider"></div>
                    ${item('driver_status.html', 'bolt', 'Update Status', 'status')}
                `}
                <div class="nav-divider"></div>
                <a href="#" class="nav-item" style="color: #ef4444;" onclick="Demo.logout(); location.href='login.html'; return false;">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a>
            </div>
        </nav>`;
    }

    function renderSidebar(s, activePage) {
        document.body.insertAdjacentHTML('afterbegin', sidebarHtml(s, activePage));
    }

    // Small "this is a demo" bar on every page
    function renderBanner() {
        const style = document.createElement('style');
        style.textContent = `
            .demo-banner { position: fixed; left: 50%; bottom: 12px; transform: translateX(-50%); z-index: 9999;
                background: #1e293b; color: #f8fafc; padding: 8px 14px; border-radius: 999px; font-size: 13px;
                display: flex; align-items: center; gap: 10px; box-shadow: 0 6px 20px rgba(0,0,0,.25);
                max-width: calc(100vw - 32px); }
            .demo-banner button { background: #6366f1; color: #fff; border: 0; border-radius: 999px; padding: 4px 10px;
                font-size: 12px; cursor: pointer; white-space: nowrap; }
            .demo-banner span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            body { padding-bottom: 60px; }
        `;
        document.head.appendChild(style);
        const bar = document.createElement('div');
        bar.className = 'demo-banner';
        bar.innerHTML = '<i class="fas fa-flask"></i><span>Demo build &middot; data is stored in this browser only</span>';
        const btn = document.createElement('button');
        btn.textContent = 'Reset data';
        btn.onclick = () => { if (confirm('Reset all demo data and log out?')) reset(); };
        bar.appendChild(btn);
        document.body.appendChild(bar);
    }

    // ---- fetch shim: answers the same URLs the PHP version serves ----
    const realFetch = window.fetch.bind(window);
    function json(data, status) {
        return Promise.resolve(new Response(JSON.stringify(data), {
            status: status || 200, headers: { 'Content-Type': 'application/json' }
        }));
    }

    // WMO weather codes (Open-Meteo) -> OpenWeather-style main/description
    function wmo(code) {
        if (code === 0) return ['Clear', 'clear sky'];
        if (code <= 2) return ['Clouds', 'partly cloudy'];
        if (code === 3) return ['Clouds', 'overcast clouds'];
        if (code <= 48) return ['Fog', 'fog'];
        if (code <= 57) return ['Drizzle', 'drizzle'];
        if (code <= 67) return ['Rain', 'rain'];
        if (code <= 77) return ['Snow', 'snow'];
        if (code <= 82) return ['Rain', 'rain showers'];
        if (code <= 86) return ['Snow', 'snow showers'];
        return ['Thunderstorm', 'thunderstorm'];
    }

    window.fetch = function (input, init) {
        const url = typeof input === 'string' ? input : input.url;

        if (url.indexOf('ajax/fetch_drivers.php') === 0) {
            return json(availableDrivers());
        }

        if (url.indexOf('ajax/update_status.php') === 0) {
            const s = session();
            if (!s) return json({ success: false, error: 'Not logged in' }, 401);
            if (s.role !== 'driver') return json({ success: false, error: 'Only drivers can update status' }, 403);
            const status = new URLSearchParams(init && init.body ? String(init.body) : '').get('status');
            if (!STATUSES[status]) return json({ success: false, error: 'Invalid status' }, 400);
            const driver = getDriverByUser(s.user_id);
            if (!driver) return json({ success: false, error: 'No driver profile is linked to this account' }, 404);
            setDriverStatus(driver, status);
            return json({ success: true, status, label: STATUSES[status].label, badge: STATUSES[status].badge });
        }

        if (url.indexOf('api/weather.php') === 0) {
            // Open-Meteo needs no API key, so it is safe to call from a public static site
            return realFetch('https://api.open-meteo.com/v1/forecast?latitude=16.6886&longitude=121.5487' +
                '&current=temperature_2m,relative_humidity_2m,surface_pressure,wind_speed_10m,weather_code&wind_speed_unit=ms')
                .then(r => r.json())
                .then(d => {
                    const c = d.current;
                    const [main, description] = wmo(c.weather_code);
                    return json({
                        name: 'Santiago City',
                        main: { temp: c.temperature_2m, humidity: c.relative_humidity_2m, pressure: Math.round(c.surface_pressure) },
                        weather: [{ main, description }],
                        wind: { speed: c.wind_speed_10m }
                    });
                });
        }

        return realFetch(input, init);
    };

    window.Demo = {
        STATUSES, db: () => db, save, session, login, logout, requireLogin,
        getDriverByUser, getDriverStatus, setDriverStatus, tricycle, availableDrivers,
        addUser, deleteUser, reset, h, fmtDate, fmtTime, isToday, renderSidebar, renderBanner
    };
})();
