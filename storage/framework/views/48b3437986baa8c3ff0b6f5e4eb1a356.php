<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-dark.png')); ?>" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-light.png')); ?>" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span><?php echo app('translator')->get('translation.menu'); ?></span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i data-feather="home" class="icon-dual"></i> <span><?php echo app('translator')->get('translation.dashboards'); ?></span>
                    </a>
                    
                </li> <!-- end Dashboard Menu -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarConfigurations" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarConfigurations">
                        <i data-feather="settings" class="icon-dual"></i> <span><?php echo app('translator')->get('translation.Configurations'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarConfigurations">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(url('/estructura')); ?>" class="nav-link"><?php echo app('translator')->get('translation.Structures'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/groups')); ?>" class="nav-link"><?php echo app('translator')->get('translation.Group'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/subgroups')); ?>" class="nav-link"><?php echo app('translator')->get('translation.SubGroup'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/subgroups1')); ?>" class="nav-link"><?php echo app('translator')->get('translation.SubGroup1'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/titles')); ?>" class="nav-link"><?php echo app('translator')->get('translation.Title'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/typedoc')); ?>" class="nav-link"><?php echo app('translator')->get('translation.TypeDoc'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/released')); ?>" class="nav-link"><?php echo app('translator')->get('translation.Released'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/typestreet')); ?>" class="nav-link"><?php echo app('translator')->get('translation.TypeStreet'); ?></a>
                            </li>
                           
                            
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCustomers" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarCustomers">
                        <i data-feather="users" class="icon-dual"></i> <span><?php echo app('translator')->get('translation.customers'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCustomers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(url('/newcustomer')); ?>" class="nav-link"><?php echo app('translator')->get('translation.new'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/customers')); ?>" class="nav-link"><?php echo app('translator')->get('translation.list'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/componenti')); ?>" class="nav-link"><?php echo app('translator')->get('translation.componente'); ?></a>
                            </li>
                            
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarTickets" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarTickets">
                        <i data-feather="calendar" class="icon-dual"></i> <span>Schedine</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarTickets">
                        <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                                <a href="<?php echo e(url('/schedina')); ?>" class="nav-link">Schedina</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/arrivals')); ?>" class="nav-link"><?php echo app('translator')->get('translation.arrivals'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/#')); ?>" class="nav-link"><?php echo app('translator')->get('translation.ticketsWeb'); ?></a>
                            </li>
                            
                            
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarInvio" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarInvio">
                        <i data-feather="cloud" class="icon-dual"></i> <span>Invio Telematico</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarInvio">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(url('/#')); ?>" class="nav-link">Tassa di soggiorno</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/#')); ?>" class="nav-link">Istat Tavola A</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/#')); ?>" class="nav-link">Questura</a>
                            </li>
                            
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarStatistica" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarStatistica">
                        <i data-feather="pie-chart" class="icon-dual"></i> <span>Statistica</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarStatistica">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(url('/#')); ?>" class="nav-link">Pressenza</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/#')); ?>" class="nav-link">Arrivi / Partenza / Presenza</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(url('/#')); ?>" class="nav-link">zione</a>
                            </li>
                            
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->

                <?php if(app()->environment(['local', 'development'])): ?>
                <li class="menu-title"><span>Dev</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDev" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDev">
                        <i data-feather="tool" class="icon-dual"></i> <span>Dev</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarDev">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?php echo e(url('/Ind_Tipo_Via_Num_Int')); ?>" class="nav-link">Ind_Tipo_Via_Num_Int</a></li>
                            <li class="nav-item"><a href="<?php echo e(url('/Indirizzo')); ?>" class="nav-link">Indirizzo</a></li>
                            <li class="nav-item"><a href="<?php echo e(url('/Nac_Reg_Prov_Citt')); ?>" class="nav-link">Nac_Reg_Prov_Citt</a></li>
                            <li class="nav-item"><a href="<?php echo e(url('/Relazione_Geografica')); ?>" class="nav-link">Relazione_Geografica</a></li>
                            <li class="nav-item"><a href="<?php echo e(url('/Geo_SelfTest')); ?>" class="nav-link">Geo_SelfTest</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>


            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
<?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/layouts/sidebar.blade.php ENDPATH**/ ?>