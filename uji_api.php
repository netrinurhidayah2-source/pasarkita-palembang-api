
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji REST API - PasarKita</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f5ef;
            color: #333;
        }

        header {
            background: #701f2b;
            color: white;
            padding: 25px 7%;
        }

        header h1 {
            margin: 0 0 8px;
        }

        header p {
            margin: 0;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .filter {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        input, select, button {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        input {
            flex: 1;
            min-width: 180px;
        }

        button {
            background: #701f2b;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #501520;
        }

        #pesan {
            margin: 15px 0;
            color: #701f2b;
        }

        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px #00000012;
        }

        .card img {
            width: 100%;
            height: 170px;
            object-fit: cover;
            background: #eee;
        }

        .card-content {
            padding: 15px;
        }

        .card h3 {
            margin-top: 0;
        }

        .harga {
            color: #701f2b;
            font-weight: bold;
            font-size: 18px;
        }

        .detail {
            display: inline-block;
            margin-top: 10px;
            color: #701f2b;
            text-decoration: none;
            font-weight: bold;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #777;
        }
    </style>
</head>

<body>

<header>
    <h1>PasarKita</h1>
    <p>Marketplace Produk Tradisional Indonesia</p>
</header>

<div class="container">

    <h2>Daftar Produk</h2>
    <p>Data produk dan kategori diambil melalui REST API.</p>

    <div class="filter">
        <input
            type="text"
            id="pencarian"
            placeholder="Cari nama produk..."
        >

        <select id="kategori">
            <option value="">Semua Kategori</option>
        </select>

        <button onclick="tampilkanProduk()">Cari</button>
    </div>

    <div id="pesan">Memuat data produk...</div>

    <div class="produk-grid" id="daftarProduk"></div>

</div>

<footer>
    PasarKita - Pengembangan REST API Marketplace
</footer>

<script>
const API_PRODUK = "api/produk.php";
const API_KATEGORI = "api/kategori.php";

async function ambilKategori() {
    try {
        const response = await fetch(API_KATEGORI);
        const hasil = await response.json();

        if (!hasil.status) {
            throw new Error(hasil.message || "Gagal mengambil kategori");
        }

        const daftar = hasil.data.kategori || [];
        const select = document.getElementById("kategori");

        daftar.forEach(item => {
            const option = document.createElement("option");
            option.value = item.id;
            option.textContent = item.nama_kategori;
            select.appendChild(option);
        });

    } catch (error) {
        console.error("Kategori:", error);
    }
}

async function tampilkanProduk() {
    const kata = document.getElementById("pencarian").value.trim();
    const kategori = document.getElementById("kategori").value;

    const parameter = new URLSearchParams();

    if (kata) parameter.append("q", kata);
    if (kategori) parameter.append("kategori_id", kategori);

    const url = API_PRODUK +
        (parameter.toString() ? "?" + parameter.toString() : "");

    const tempat = document.getElementById("daftarProduk");
    const pesan = document.getElementById("pesan");

    tempat.innerHTML = "";
    pesan.textContent = "Memuat produk...";

    try {
        const response = await fetch(url);
        const hasil = await response.json();

        if (!response.ok || !hasil.status) {
            throw new Error(hasil.message || "Gagal mengambil produk");
        }

        const produk = hasil.data.produk || [];

        if (produk.length === 0) {
            pesan.textContent = "Produk tidak ditemukan.";
            return;
        }

        pesan.textContent = "Berhasil menampilkan " +
            produk.length + " produk.";

        produk.forEach(item => {
            const card = document.createElement("div");
            card.className = "card";

            const gambar = item.foto
                ? "uploads/" + item.foto
                : "";

            card.innerHTML = `
                <img
                    src="${gambar}"
                    alt="Foto produk"
                    onerror="this.style.display='none'"
                >

                <div class="card-content">
                    <h3></h3>
                    <p class="harga"></p>
                    <p class="stok"></p>
                    <a class="detail">Lihat Detail</a>
                </div>
            `;

            card.querySelector("h3").textContent =
                item.nama_produk || "Produk";

            card.querySelector(".harga").textContent =
                "Rp " + Number(item.harga || 0).toLocaleString("id-ID");

            card.querySelector(".stok").textContent =
                "Stok: " + (item.stok ?? 0);

            card.querySelector(".detail").href =
                "detail.php?id=" + encodeURIComponent(item.id);

            tempat.appendChild(card);
        });

    } catch (error) {
        pesan.textContent = "Terjadi kesalahan: " + error.message;
        console.error(error);
    }
}

document.getElementById("pencarian")
    .addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            tampilkanProduk();
        }
    });

ambilKategori();
tampilkanProduk();
</script>

</body>
</html>
