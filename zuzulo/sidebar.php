<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="<?php echo $alamat_admin.'dasbor'; ?>" class="app-brand-link">
      <span class="app-brand-logo demo">
        <img src="assets/img/logo.png" alt="Logo">
      </span>
      <span class="app-brand-text demo menu-text fw-bold ms-2">Panel Admin</span>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M11.4854 4.88844C11.0081 4.41121 10.2344 4.41121 9.75715 4.88844L4.51028 10.1353C4.03297 10.6126 4.03297 11.3865 4.51028 11.8638L9.75715 17.1107C10.2344 17.5879 11.0081 17.5879 11.4854 17.1107C11.9626 16.6334 11.9626 15.8597 11.4854 15.3824L7.96672 11.8638C7.48942 11.3865 7.48942 10.6126 7.96672 10.1353L11.4854 6.61667C11.9626 6.13943 11.9626 5.36568 11.4854 4.88844C17.1192 4.41121 16.3455 4.41121 15.8683 4.88844L10.6214 10.1353C10.1441 10.6126 10.1441 11.3865 10.6214 11.8638L15.8683 17.1107C16.3455 17.5879 17.1192 17.5879 17.5965 17.1107C18.0737 16.6334 18.0737 15.8597 17.5965 15.3824L14.0778 11.8638C13.6005 11.3865 13.6005 10.6126 14.0778 10.1353L17.5965 6.61667C18.0737 6.13943 18.0737 5.36568 17.5965 4.88844C17.1192 4.41121 16.3455 4.41121 15.8683 4.88844Z" fill="currentColor" fill-opacity="0.6"/>
        <path d="M15.8683 4.88844L10.6214 10.1353C10.1441 10.6126 10.1441 11.3865 10.6214 11.8638L15.8683 17.1107C16.3455 17.5879 17.1192 17.5879 17.5965 17.1107C18.0737 16.6334 18.0737 15.8597 17.5965 15.3824L14.0778 11.8638C13.6005 11.3865 13.6005 10.6126 14.0778 10.1353L17.5965 6.61667C18.0737 6.13943 18.0737 5.36568 17.5965 4.88844C17.1192 4.41121 16.3455 4.41121 15.8683 4.88844Z" fill="currentColor" fill-opacity="0.38"/>
      </svg>
    </a>
  </div>
  <div class="menu-inner-shadow"></div>
  <ul class="menu-inner py-1">
    <li class="menu-item" id="dasbor">
      <a href="<?php echo $alamat_admin.'dasbor'; ?>" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-monitor-dashboard"></i>
        <div>Dasbor</div>
      </a>
    </li>

    <li class="menu-header fw-light mt-4">
      <span class="menu-header-text">Menu Utama</span>
    </li>

    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-account-supervisor-circle"></i>
        <div>Pengguna</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item" id="anggota">
          <a href="<?php echo $alamat_admin.'anggota'; ?>" class="menu-link">
            <div>Anggota</div>
          </a>
        </li>
        <li class="menu-item" id="kyc">
          <a href="<?php echo $alamat_admin.'kyc'; ?>" class="menu-link">
            <div>KYC</div>
          </a>
        </li>
        <li class="menu-item" id="rekap">
          <a href="<?php echo $alamat_admin.'rekap'; ?>" class="menu-link">
            <div>Rekapan</div>
          </a>
        </li>
        <li class="menu-item" id="refferal">
          <a href="<?php echo $alamat_admin.'refferal'; ?>" class="menu-link">
            <div>Refferal</div>
          </a>
        </li>
        <li class="menu-item" id="turnover">
          <a href="<?php echo $alamat_admin.'turnover'; ?>" class="menu-link">
            <div>Turnover Member</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-swap-horizontal-bold"></i>
        <div>Transaksi</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item" id="deposit">
          <a href="<?php echo $alamat_admin.'deposit'; ?>" class="menu-link">
            <div>Deposit</div>
          </a>
        </li>
        <li class="menu-item" id="withdraw">
          <a href="<?php echo $alamat_admin.'withdraw'; ?>" class="menu-link">
            <div>Withdraw</div>
          </a>
        </li>
        <li class="menu-item" id="saldo">
          <a href="<?php echo $alamat_admin.'saldo'; ?>" class="menu-link">
            <div>Saldo</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item" id="tambah_domain"> 
  <a href="<?php echo $alamat_admin.'tambah_domain.php'; ?>" class="menu-link">
    <i class="menu-icon tf-icons mdi mdi-web"></i>
      <div>Tambah Domain</div>
    </a>
  </li>
    
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-palette-swatch"></i>
        <div>Konten & Marketing</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item" id="promosi">
          <a href="<?php echo $alamat_admin.'promosi'; ?>" class="menu-link">
            <div>Promosi</div>
          </a>
        </li>
        <li class="menu-item" id="ubah_pengaturan_referral">
          <a href="<?php echo $alamat_admin.'ubah_pengaturan_referral'; ?>" class="menu-link">
            <div>Refferal & Bonus</div>
          </a>
        </li>
        <li class="menu-item" id="gamepopuler">
          <a href="<?php echo $alamat_admin.'gamepopuler'; ?>" class="menu-link">
            <div>Game Populer</div>
          </a>
        </li>
        <li class="menu-item" id="gamerecomen">
          <a href="<?php echo $alamat_admin.'gamerecomen'; ?>" class="menu-link">
            <div>Game Rekomendasi</div>
          </a>
        </li>
        <li class="menu-item" id="bonus">
          <a href="<?php echo $alamat_admin.'bonus'; ?>" class="menu-link">
            <div>Bonus</div>
          </a>
        </li>
        <li class="menu-item" id="claimbonus">
          <a href="<?php echo $alamat_admin.'claim_bonus'; ?>" class="menu-link">
            <div>Claim Bonus</div>
          </a>
        </li>
        <li class="menu-item" id="ikon_mengambang">
          <a href="<?php echo $alamat_admin.'ikon_mengambang'; ?>" class="menu-link">
            <div>Ikon Mengambang</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-palette-swatch"></i>
        <div>Game Pilihan</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item" id="pilihan_lottery">
          <a href="<?php echo $alamat_admin.'pilihan_lottery'; ?>" class="menu-link">
            <div>Togel</div>
          </a>
        </li> 
        <li class="menu-item" id="pilihan_slot">
          <a href="<?php echo $alamat_admin.'pilihan_slot'; ?>" class="menu-link">
            <div>Slot</div>
          </a>
        </li> 
        <li class="menu-item" id="pilihan_casino">
          <a href="<?php echo $alamat_admin.'pilihan_casino'; ?>" class="menu-link">
            <div>Casino</div>
          </a>
        </li> 
        <li class="menu-item" id="pilihan_table">
          <a href="<?php echo $alamat_admin.'pilihan_table'; ?>" class="menu-link">
            <div>Table</div>
          </a>
        </li> 
        <li class="menu-item" id="pilihan_sports">
          <a href="<?php echo $alamat_admin.'pilihan_sports'; ?>" class="menu-link">
            <div>Sports</div>
          </a>
        </li> 
        <li class="menu-item" id="pilihan_arcade">
          <a href="<?php echo $alamat_admin.'pilihan_arcade'; ?>" class="menu-link">
            <div>Arcade</div>
          </a>
        </li>
        <li class="menu-item" id="pilihan_card">
          <a href="<?php echo $alamat_admin.'pilihan_card'; ?>" class="menu-link">
            <div>Poker</div>
          </a>
        </li> 
        <li class="menu-item" id="pilihan_fishing">
          <a href="<?php echo $alamat_admin.'pilihan_fishing'; ?>" class="menu-link">
            <div>Fishing</div>
          </a>
        </li>
        <li class="menu-item" id="pilihan_cockfight">
          <a href="<?php echo $alamat_admin.'pilihan_cockfight'; ?>" class="menu-link">
            <div>CockFight</div>
          </a>
        </li>
        <li class="menu-item" id="pilihan_crash">
          <a href="<?php echo $alamat_admin.'pilihan_crash'; ?>" class="menu-link">
            <div>Crash</div>
          </a>
        </li>            
      </ul>
    </li>

    <li class="menu-item" id="rekening"> 
      <a href="<?php echo $alamat_admin.'rekening'; ?>" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-bank"></i>
        <div>Rekening</div>
      </a>
    </li>

    <li class="menu-item" id="staff"> 
      <a href="<?php echo $alamat_admin.'staff'; ?>" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-account-group"></i>
        <div>Staff</div>
      </a>
    </li>    

    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-api"></i>
        <div>GameXaGlobal</div>
      </a>
      <ul class="menu-sub">
         <li class="menu-item">
          <a href="<?php echo $alamat_admin.'srg_provider'; ?>" class="menu-link">
            <div>ProviderList</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="<?php echo $alamat_admin.'srg_game'; ?>" class="menu-link">
            <div>Gamelist</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="<?php echo $alamat_admin.'exa_transaction'; ?>" class="menu-link">
            <div>All Transaction</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="<?php echo $alamat_admin.'exa_stats'; ?>" class="menu-link">
            <div>Stats</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-api"></i>
        <div>Nexus GGR</div>
      </a>
      <ul class="menu-sub">
         <li class="menu-item">
          <a href="<?php echo $alamat_admin.'nexus_provider'; ?>" class="menu-link">
            <div>ProviderList</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="<?php echo $alamat_admin.'nexus_gamelist'; ?>" class="menu-link">
            <div>Gamelist</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="<?php echo $alamat_admin.'nexus_transaction'; ?>" class="menu-link">
            <div>All Transaction</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item" id="voucher"> 
      <a href="<?php echo $alamat_admin.'voucher'; ?>" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-bank"></i>
        <div>Voucher</div>
      </a>
    </li>

    <li class="menu-header fw-light mt-4">
      <span class="menu-header-text">Menu Lainnya</span>
    </li>
    <li class="menu-item" id="profil">
      <a href="<?php echo $alamat_admin.'profil'; ?>" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-account"></i>
        <div>Profil</div>
      </a>
    </li>
    <li class="menu-item" id="pengaturan">
      <a href="<?php echo $alamat_admin.'pengaturan'; ?>" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-cog"></i>
        <div>Pengaturan</div>
      </a>
    </li>
    <li class="menu-item">
      <a href="<?php echo $alamat_admin.'keluar.php'; ?>" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-power"></i>
        <div>Keluar</div>
      </a>
    </li>
  </ul>
</aside>
