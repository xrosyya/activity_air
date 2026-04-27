<?php
include './assets/func.php';
$air     = new klas_air;
$koneksi = $air->koneksi();

$daftar = [
    [
        'foto'         => './assets/img/fla.jpeg',
        'nim'          => '3.33.24.1.07',
        'nama'         => 'Flavia Adira Anjani',
        'prodi'        => 'D3 Teknik Telekomunikasi',
        'angkatan'     => '2024',
        'kelas'        => 'TK-2B',
        'no_hp'        => '+62 821-3699-9552',
        'email'        => 'flaviaanjani05@gmail.com',
        'asal_sekolah' => 'SMA',
        'nama_sekolah' => 'SMA Negeri 13 Semarang',
        'jurusan'      => 'IPA',
        'alamat'       => 'Semarang',
        'hobi'         => 'Menyanyi, Travelling',
        'motto'        => 'It will pass, everything youve gone through it will pass',
        'instagram'    => 'https://www.instagram.com/flaaviaaq?igsh=MWdhcTBzNDQzamY3Ng==',
        'linkedin'     => '',
        'github'       => '',
    ],
    [
        'foto'         => './assets/img/ros.jpeg',
        'nim'          => '3.33.24.1.21',
        'nama'         => 'Rosita Dwi Anggraini',
        'prodi'        => 'D3 Teknik Telekomunikasi',
        'angkatan'     => '2024',
        'kelas'        => 'TK-2B',
        'no_hp'        => '+62 856-4364-8840',
        'email'        => 'rosrositaa04@gmail.com',
        'asal_sekolah' => 'SMK',
        'nama_sekolah' => 'SMK Telkom Sandy Putra Purwokerto',
        'jurusan'      => 'Rekayasa Perangkat Lunak',
        'alamat'       => 'Banjarnegara',
        'hobi'         => 'Desain, Mendengarkan Musik, Travelling',
        'motto'        => 'Nothing Imposible',
        'instagram'    => 'https://www.instagram.com/yyrositaa?igsh=dGxxbWp4b2Zvc2Ix&utm_source=qr',
        'linkedin'     => '',
        'github'       => 'https://github.com/xrosyya',
    ],
];

function inisial($nama) {
    $out = '';
    foreach (explode(' ', $nama) as $k) if (!empty($k)) $out .= strtoupper($k[0]);
    return substr($out, 0, 2);
}
function fotoAda($p) { return !empty($p) && file_exists($p); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Kelompok 07 – AirSystem</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Instrument+Serif:ital@0;1&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
<script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --rose:#d63f72;
    --rose2:#e8648e;
    --blush:#f9c5d5;
    --pale:#fff5f8;
    --ink:#180d12;
    --muted:#8c6070;
    --line:rgba(214,63,114,.12);
    --fs:'Syne',sans-serif;
    --fi:'Instrument Serif',Georgia,serif;
    --fb:'DM Sans',sans-serif;
}
html{scroll-behavior:smooth}
body{font-family:var(--fb);background:var(--pale);color:var(--ink);overflow-x:hidden}
a{text-decoration:none;color:inherit}

/* TOPBAR */
.bar{
    position:sticky;top:0;z-index:100;
    display:flex;align-items:center;justify-content:space-between;
    padding:0 40px;height:56px;
    background:rgba(255,245,248,.9);
    backdrop-filter:blur(20px);
    border-bottom:1px solid var(--line);
}
.bar-logo{font-family:var(--fs);font-size:.88rem;font-weight:700;letter-spacing:.03em}
.bar-logo em{color:var(--rose);font-style:normal}
.bar-back{
    display:inline-flex;align-items:center;gap:8px;
    font-family:var(--fs);font-size:.68rem;font-weight:700;
    letter-spacing:.1em;text-transform:uppercase;
    color:var(--rose);border:1.5px solid var(--rose);
    padding:6px 18px;border-radius:4px;
    transition:all .18s;
}
.bar-back:hover{background:var(--rose);color:#fff}

/* INTRO */
.intro{
    max-width:1100px;margin:0 auto;
    padding:88px 40px 64px;
    display:flex;align-items:flex-end;justify-content:space-between;gap:40px;
    flex-wrap:wrap;
}
.intro-eyebrow{
    display:flex;align-items:center;gap:12px;
    font-family:var(--fs);font-size:.62rem;font-weight:600;
    letter-spacing:.24em;text-transform:uppercase;color:var(--muted);
    margin-bottom:22px;
}
.intro-eyebrow span{display:block;width:32px;height:1px;background:var(--rose)}
.intro-h1{
    font-family:var(--fi);
    font-size:clamp(3.5rem,7vw,6.5rem);
    font-weight:400;line-height:.92;
    letter-spacing:-.04em;color:var(--ink);
}
.intro-h1 i{font-style:italic;color:var(--rose)}
.intro-desc{
    max-width:280px;
    font-size:.99rem;font-weight:300;line-height:1.8;
    color:var(--muted);padding-bottom:6px;
}

/* DIVIDER */
.hdiv{
    max-width:1100px;margin:0 auto 0;
    border:none;border-top:1px solid var(--line);
}

/* COUNTER STRIP */
.strip{
    max-width:1100px;margin:0 auto 72px;
    padding:0 40px;
    display:grid;grid-template-columns:repeat(3,1fr);
}
.strip-item{
    padding:32px 0 32px 40px;
    border-right:1px solid var(--line);
}
.strip-item:first-child{padding-left:0}
.strip-item:last-child{border:none}
.strip-num{
    font-family:var(--fi);font-style:italic;
    font-size:2.2rem;color:var(--rose);line-height:1;
    margin-bottom:4px;
}
.strip-lbl{
    font-family:var(--fs);font-size:.8rem;font-weight:700;
    letter-spacing:.2em;text-transform:uppercase;color:var(--muted);
}

/* PROFILE BLOCKS */
.profiles{max-width:1100px;margin:0 auto;padding:0 40px 100px}

.profile-block{
    display:grid;
    grid-template-columns:5fr 4fr;
    min-height:580px;
    border-radius:16px;
    overflow:hidden;
    border:1px solid var(--line);
    background:#fff;
    margin-bottom:36px;
    transition:box-shadow .4s ease;
}
.profile-block:hover{
    box-shadow:0 28px 80px -16px rgba(214,63,114,.2);
}
/* alternating: foto kanan untuk profil ke-2 */
.profile-block.flip{grid-template-columns:4fr 5fr}
.profile-block.flip .pb-photo{order:2}
.profile-block.flip .pb-info{order:1}

/* FOTO SISI */
.pb-photo{
    position:relative;overflow:hidden;
    background:var(--ink);
}
.pb-photo img{
    width:100%;height:100%;
    object-fit:cover;object-position:center top;
    display:block;
    filter:saturate(.88);
    transition:transform .8s cubic-bezier(.22,1,.36,1),filter .5s;
}
.profile-block:hover .pb-photo img{
    transform:scale(1.05);filter:saturate(1);
}
.pb-photo::after{
    content:'';
    position:absolute;inset:0;
    background:linear-gradient(
        0deg,
        rgba(18,6,13,.75) 0%,
        rgba(18,6,13,.06) 50%,
        transparent 70%
    );
    pointer-events:none;
}
.pb-num{
    position:absolute;top:20px;left:20px;z-index:2;
    font-family:var(--fs);font-size:.40rem;font-weight:500;
    letter-spacing:.20rem;text-transform:uppercase;
    color:rgba(255,255,255,.65);
    background:rgba(255,255,255,.1);
    backdrop-filter:blur(10px);
    padding:5px 12px;border-radius:3px;
}
.pb-caption{
    position:absolute;bottom:0;left:0;right:0;
    z-index:2;padding:28px 28px 24px;
}
.pc-nim{
    font-family:var(--fs);font-size:.55rem;font-weight:600;
    letter-spacing:.18em;text-transform:uppercase;
    color:rgba(255,255,255,.5);margin-bottom:7px;
}
.pc-name{
    font-family:var(--fi);font-style:italic;
    font-size:1.9rem;color:#fff;line-height:1.1;
}
.pc-pill{
    display:inline-flex;align-items:center;gap:6px;
    margin-top:12px;
    font-family:var(--fs);font-size:.6rem;font-weight:600;
    letter-spacing:.1em;text-transform:uppercase;
    color:rgba(255,255,255,.75);
    background:rgba(255,255,255,.12);
    backdrop-filter:blur(6px);
    padding:5px 12px;border-radius:3px;
}
.pc-dot{
    width:6px;height:6px;border-radius:50%;
    background:#4ade80;box-shadow:0 0 8px #4ade8088;
}

/* INFO SISI */
.pb-info{
    padding:40px 44px;
    display:flex;flex-direction:column;
}
.pi-tag{
    display:inline-block;
    font-family:var(--fs);font-size:.58rem;font-weight:700;
    letter-spacing:.2em;text-transform:uppercase;
    color:var(--rose);background:rgba(214,63,114,.07);
    padding:5px 14px;border-radius:3px;
    margin-bottom:16px;width:fit-content;
}
.pi-name{
    font-family:var(--fi);font-size:2.1rem;
    font-weight:400;letter-spacing:-.02em;
    line-height:1.05;color:var(--ink);margin-bottom:8px;
}
.pi-sub{
    font-size:.78rem;font-weight:300;color:var(--muted);
    display:flex;align-items:center;gap:10px;
    padding-bottom:22px;
    border-bottom:1px solid var(--line);
    margin-bottom:20px;
}
.pi-sub::before{content:'';display:block;width:20px;height:1px;background:var(--blush)}

/* motto */
.pi-motto{
    padding:14px 0 14px 16px;
    border-left:2px solid var(--rose);
    margin-bottom:22px;
}
.pi-motto p{
    font-family:var(--fi);font-style:italic;
    font-size:1rem;line-height:1.6;color:var(--ink);
}

/* info rows */
.pi-section{
    font-family:var(--fs);font-size:.57rem;font-weight:700;
    letter-spacing:.2em;text-transform:uppercase;color:var(--rose);
    display:flex;align-items:center;gap:10px;
    margin:16px 0 10px;
}
.pi-section::after{content:'';flex:1;height:1px;background:var(--line)}
.pi-row{
    display:flex;justify-content:space-between;align-items:baseline;
    padding:8px 0;border-bottom:1px solid var(--line);gap:12px;
}
.pi-row:last-of-type{border:none}
.pi-k{font-size:.73rem;color:var(--muted);flex-shrink:0}
.pi-v{font-size:.78rem;font-weight:500;color:var(--ink);text-align:right}
.pi-v.rose{color:var(--rose);font-weight:600}

/* sosmed */
.pi-socials{
    display:flex;gap:8px;margin-top:auto;
    padding-top:22px;border-top:1px solid var(--line);
}
.soc{
    width:36px;height:36px;border-radius:6px;
    border:1px solid var(--line);
    display:flex;align-items:center;justify-content:center;
    font-size:.78rem;color:var(--muted);
    transition:all .18s;
}
.soc:hover{border-color:var(--rose);color:var(--rose);background:rgba(214,63,114,.06)}

/* FOOTER */
.foot{
    border-top:1px solid var(--line);
    padding:24px 40px;
    display:flex;align-items:center;justify-content:space-between;
    font-size:.72rem;color:var(--muted);flex-wrap:wrap;gap:10px;
}
.foot a{color:var(--rose)}
.foot a:hover{text-decoration:underline}
.foot-brand{font-family:var(--fs);font-weight:700;font-size:.78rem;color:var(--ink)}

/* RESPONSIVE */
@media(max-width:760px){
    .bar{padding:0 20px}
    .intro{padding:52px 20px 44px;gap:24px}
    .intro-desc{display:none}
    .strip{padding:0 20px;grid-template-columns:1fr 1fr}
    .strip-item:first-child{padding-left:0}
    .strip-item:nth-child(3){grid-column:1/-1;border-right:none;border-top:1px solid var(--line)}
    .profiles{padding:0 20px 60px}
    .profile-block,.profile-block.flip{
        grid-template-columns:1fr;min-height:auto;
    }
    .profile-block.flip .pb-photo,.profile-block.flip .pb-info{order:unset}
    .pb-photo{height:75vw;min-height:260px}
    .pb-info{padding:24px 22px}
    .foot{padding:18px 20px}
}
</style>
</head>
<body>

<!-- TOPBAR -->
<nav class="bar">
    <div class="bar-logo">Air<em>System</em> &nbsp;·&nbsp; Kelompok07</div>
    <a href="index.php" class="bar-back"><i class="fa fa-arrow-left"></i> &nbsp;Login</a>
</nav>

<!-- INTRO HERO -->
<div class="intro">
    <div>
        <div class="intro-eyebrow">
            <span></span>
            Project Pemrograman Web dan Database &nbsp;·&nbsp; 
        </div>
        <h1 class="intro-h1"><i> Hello !</i> <br> Meet the Team.</h1>
    </div>
    <p class="intro-desc">
        Kami dari kelompok 07  —
        percaya bahwa great things happen when logic meets a vision.
    </p>
</div>

<hr class="hdiv"/>

<!-- STATS -->
<div class="strip">
    <div class="strip-item">
        <div class="strip-num">TK-2B</div>
        <div class="strip-lbl">Kelas</div>
    </div>
    <div class="strip-item">
        <div class="strip-num">'07</div>
        <div class="strip-lbl">Kelompok</div>
    </div>
    <div class="strip-item">
        <div class="strip-num">Politeknik Negeri Semarang</div>
        <div class="strip-lbl">Teknik Elektro</div>
    </div>
</div>

<!-- PROFILES -->
<div class="profiles">

<?php foreach ($daftar as $i => $m):
    $ada  = fotoAda($m['foto']);
    $ini  = inisial($m['nama']);
    $no   = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
    $flip = ($i % 2 !== 0) ? 'flip' : '';
?>

<div class="profile-block <?= $flip ?>">

    <!-- FOTO -->
    <div class="pb-photo">
        <?php if ($ada): ?>
            <img src="<?= htmlspecialchars($m['foto']) ?>"
                 alt="<?= htmlspecialchars($m['nama']) ?>"/>
        <?php else: ?>
            <div style="position:absolute;inset:0;display:flex;align-items:center;
                        justify-content:center;font-family:var(--fi);font-size:6rem;
                        color:rgba(255,255,255,.2);z-index:1">
                <?= $ini ?>
            </div>
        <?php endif; ?>

        <div class="pb-num">No. <?= $no ?></div>

        <div class="pb-caption">
            <div class="pc-nim"><?= htmlspecialchars($m['nim']) ?></div>
            <div class="pc-name"><?= htmlspecialchars($m['nama']) ?></div>
            <div class="pc-pill">
                <span class="pc-dot"></span>
                Aktif &nbsp;·&nbsp; <?= htmlspecialchars($m['angkatan']) ?>
            </div>
        </div>
    </div>

    <!-- INFO -->
    <div class="pb-info">
        <div class="pi-tag"><?= htmlspecialchars($m['prodi']) ?></div>
        <h2 class="pi-name"><?= htmlspecialchars($m['nama']) ?></h2>
        <div class="pi-sub">
            Nim <?= htmlspecialchars($m['nim']) ?>
            &nbsp;·&nbsp;
            <?= htmlspecialchars($m['kelas']) ?>
        </div>

        <?php if (!empty($m['motto'])): ?>
        <div class="pi-motto">
            <p>"<?= htmlspecialchars($m['motto']) ?>"</p>
        </div>
        <?php endif; ?>

        <div class="pi-section">Kontak</div>
        <div class="pi-row">
            <span class="pi-k">Telepon</span>
            <span class="pi-v"><?= htmlspecialchars($m['no_hp']) ?></span>
        </div>
        <?php if (!empty($m['email'])): ?>
        <div class="pi-row">
            <span class="pi-k">Email</span>
            <span class="pi-v" style="font-size:.71rem"><?= htmlspecialchars($m['email']) ?></span>
        </div>
        <?php endif; ?>

        <div class="pi-section">Pendidikan</div>
        <div class="pi-row">
            <span class="pi-k">Jenis</span>
            <span class="pi-v"><?= htmlspecialchars($m['asal_sekolah']) ?></span>
        </div>
        <div class="pi-row">
            <span class="pi-k">Sekolah</span>
            <span class="pi-v"><?= htmlspecialchars($m['nama_sekolah']) ?></span>
        </div>
        <?php if (!empty($m['jurusan'])): ?>
        <div class="pi-row">
            <span class="pi-k">Jurusan</span>
            <span class="pi-v rose"><?= htmlspecialchars($m['jurusan']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($m['hobi'])): ?>
        <div class="pi-row">
            <span class="pi-k">Hobi</span>
            <span class="pi-v"><?= htmlspecialchars($m['hobi']) ?></span>
        </div>
        <?php endif; ?>

        <!-- SOSMED -->
        <div class="pi-socials">
            <?php if (!empty($m['instagram'])): ?>
            <a href="<?= htmlspecialchars($m['instagram']) ?>" target="_blank" class="soc" title="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
            <?php endif; ?>
            <?php if (!empty($m['linkedin'])): ?>
            <a href="<?= htmlspecialchars($m['linkedin']) ?>" target="_blank" class="soc" title="LinkedIn">
                <i class="fab fa-linkedin-in"></i>
            </a>
            <?php endif; ?>
            <?php if (!empty($m['github'])): ?>
            <a href="<?= htmlspecialchars($m['github']) ?>" target="_blank" class="soc" title="GitHub">
                <i class="fab fa-github"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php endforeach; ?>
</div>

<!-- FOOTER -->
<footer class="foot">
    <div class="foot-brand">AirSystem &nbsp;·&nbsp; Kelompok07</div>
    <div>Sistem Informasi Air &nbsp;·&nbsp; Politeknik Negeri Semarang</div>
    <a href="index.php">← Kembali ke Login</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>