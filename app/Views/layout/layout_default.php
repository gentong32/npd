<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NPD</title>
    <link rel="stylesheet" href="<?= base_url() ?>public/css/style.css?v2.4">
    <link rel="stylesheet" href="<?= base_url() ?>public/css/button.css?v2.3">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <?= $this->renderSection('style') ?>
    <link rel="icon" href="<?php echo base_url(); ?>public/images/logotut.png" type="image/gif" sizes="16x16">
    <style>
        a {
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">
            <img src="<?= base_url() ?>public/images/logotut.png" alt="Logo">
            <span>NPD - Neraca Pendidikan Daerah</span>
        </div>
        <div class="user-group">
            <button id="toggleSidebar">&#9776;</button>
        </div>
    </div>
    <div class="container">
        <div class="sidebar">
            <div class="home"><a href="<?= base_url() ?>">Beranda</a></div>
            <div id="tree-container"></div>
        </div>
        <div class="content">
            <?= $this->renderSection('konten') ?>
            <div class="alamat">
                <p>&copy; 2024 Pusdatin Kemendikbudristek</p>
            </div>
        </div>
    </div>



</body>

</html>

<script>
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');

        sidebar.style.width = sidebar.style.width === '280px' ? '0' : '280px';

    });
</script>
<script src="<?= base_url('public/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('public/js/jstree.min.js') ?>"></script>

<script>
    var batastahun = 2022;
    var namafile;
    $(document).ready(function() {
        // Data dari hasil query
        var data_wilayah = [
            <?php foreach ($data_wilayah as $data) {
                if (substr($data->kode_wilayah, 0, 2) == '35')
                    continue;
                $mstkode = ($data->mst_kode_wilayah == "") ? 'NULL' : trim($data->mst_kode_wilayah);
                echo "{
                    kode_wilayah: '" . trim($data->kode_wilayah) . "',
                    nama: '" . $data->nama . "',
                    id_level_wilayah: " . $data->id_level_wilayah . ",
                    mst_kode_wilayah: '" . $mstkode . "'},";
            }; ?>
        ];

        function buildTree(data, parent, parentKodeWilayah = null, parentNamaWilayah = null) {
            var tree = [];
            data.forEach(function(item) {
                if (item.mst_kode_wilayah === parent) {
                    // Membangun node untuk item saat ini
                    var node = {
                        text: item.nama,
                        data: {
                            kode_wilayah: item.kode_wilayah,
                            nama_wilayah: item.nama,
                            parent_kode_wilayah: parentKodeWilayah,
                            parent_nama_wilayah: parentNamaWilayah
                        } // Menyimpan kode_wilayah dan nama_wilayah dalam data node
                    };

                    // Memanggil rekursif untuk mencari children
                    var children = buildTree(data, item.kode_wilayah, item.kode_wilayah, item.nama);

                    if (item.id_level_wilayah === 0) {
                        node.state = {
                            opened: true
                        };
                    }
                    if (children.length > 0) {
                        node.children = children;
                    }
                    tree.push(node);
                }
            });
            return tree;
        }

        // Membentuk tree data dari root '000000' (Indonesia)
        var treeData = buildTree(data_wilayah, '000000');

        // Menambahkan node "Indonesia" sebagai root
        treeData = [{
            text: "Indonesia",
            state: {
                opened: true
            },
            children: treeData,
            data: {
                kode_wilayah: '000000',
                nama_wilayah: ''
            } // Menyimpan kode_wilayah untuk Indonesia
        }];

        // Inisialisasi jsTree dengan data
        $('#tree-container').jstree({
            'core': {
                'data': treeData
            }
        });

        // Event handler untuk membuka/tutup node saat diklik
        $('#tree-container').on("select_node.jstree", function(e, data) {
            // data.instance.toggle_node(data.node);

            // Menampilkan alert dengan kode_wilayah dari node yang diklik
            var kode_wilayah = data.node.data.kode_wilayah;
            var nama_wilayah = data.node.data.nama_wilayah;
            var parent_kode_wilayah = data.node.data.parent_kode_wilayah;
            var parent_nama_wilayah = data.node.data.parent_nama_wilayah;
            var namafolder = kode_wilayah.substring(0, 2) + '0000/';
            nama_wilayah = nama_wilayah.replace("Prov.", "Provinsi");
            nama_wilayah = nama_wilayah.replace('Kab.', 'Kabupaten');
            if (parent_nama_wilayah != null) {
                parent_nama_wilayah = parent_nama_wilayah.replace('Prov.', 'Provinsi');
                parent_nama_wilayah = parent_nama_wilayah.replace('Kab.', 'Kabupaten');
            }

            mst_kode = kode_wilayah.substring(0, 2) + "0000";

            if (kode_wilayah == '000000')
                namafile = "350000_NPD Nasional_";
            else {
                if (kode_wilayah.substring(2, 4) == "00")
                    namafile = kode_wilayah + "/" + kode_wilayah + "_" + nama_wilayah + "_";
                else
                    namafile = mst_kode + "/" + kode_wilayah + "_" + nama_wilayah + "_";
            }

            showPDF(namafile, batastahun);
        });

        var searchInput = document.getElementById('search-input');
        var searchResults = document.getElementById('search-results');
        var yearDropdown = document.getElementById('year-dropdown');

        function searchPredictions() {
            var searchTerm = searchInput.value.toLowerCase();
            if (searchTerm.length > 0) {
                var matches = data_wilayah.filter(function(item) {
                    return item.nama.toLowerCase().includes(searchTerm);
                }).slice(0, 8);
                displayResults(matches);
            } else {
                clearResults();
            }
        }

        function displayResults(matches) {
            var html = '';
            matches.forEach(function(match) {
                html += `<li data-kode="${match.kode_wilayah}" data-nama="${match.nama}" data-parentkode="${match.mst_kode_wilayah}" data-parentnama="${match.parent_nama}">${match.nama}</li>`;
            });

            searchResults.innerHTML = html;

            // Menambahkan event listener untuk setiap hasil prediksi
            var resultItems = searchResults.getElementsByTagName('li');
            for (var i = 0; i < resultItems.length; i++) {
                resultItems[i].addEventListener('click', function() {
                    var kode_wilayah = this.getAttribute('data-kode');
                    var nama_wilayah = this.getAttribute('data-nama');
                    var parent_kode_wilayah = this.getAttribute('data-parentkode');
                    var parent_nama_wilayah = this.getAttribute('data-parentnama');
                    nama_wilayah = nama_wilayah.replace("Prov.", "Provinsi");
                    nama_wilayah = nama_wilayah.replace('Kab.', 'Kabupaten');
                    if (parent_nama_wilayah != null) {
                        parent_nama_wilayah = parent_nama_wilayah.replace('Prov.', 'Provinsi');
                        parent_nama_wilayah = parent_nama_wilayah.replace('Kab.', 'Kabupaten');
                    }

                    mst_kode = kode_wilayah.substring(0, 2) + "0000";

                    if (kode_wilayah == '000000')
                        namafile = "350000_NPD Nasional_";
                    else {
                        if (kode_wilayah.substring(2, 4) == "00")
                            namafile = kode_wilayah + "/" + kode_wilayah + "_" + nama_wilayah + "_";
                        else
                            namafile = mst_kode + "/" + kode_wilayah + "_" + nama_wilayah + "_";
                    }

                    showPDF(namafile, batastahun);

                    searchInput.value = this.textContent;
                    clearResults(); // Menghapus daftar hasil prediksi setelah hasil dipilih
                });
            }
        }

        // Fungsi untuk menghapus hasil pencarian prediksi
        function clearResults() {
            searchResults.innerHTML = '';
        }

        // Event listener untuk input saat diketik
        searchInput.addEventListener('input', searchPredictions);

        yearDropdown.addEventListener('change', ubahtahun);

        function ubahtahun() {
            batastahun = yearDropdown.value;
            if (namafile)
                showPDF(namafile, batastahun);
        }

        // Fungsi untuk mengisi dropdown tahun
        function populateYearDropdown(startYear, endYear) {
            for (var year = endYear; year >= startYear; year--) {
                var option = document.createElement('option');
                option.value = year;
                option.text = year;
                yearDropdown.appendChild(option);
            }
        }

        // Event listener untuk input saat diketik
        searchInput.addEventListener('input', searchPredictions);
        // batastahun = new Date().getFullYear();
        // batastahun = "2022";
        populateYearDropdown(2019, batastahun);
    });
</script>

<?= $this->renderSection('script') ?>