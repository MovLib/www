# Define: movlib::initscript
define movlib::initscript {

  file { "/etc/init.d/${name}":
    ensure  => 'link',
    owner   => 'root',
    group   => 'root',
    mode    => '0755',
    recurse => true,
    source  => "${document_root}/bin/init-${name}.sh",
  }

  service { "${name}":
    ensure     => true,
    enable     => true,
    hasrestart => true,
    path       => '/etc/init.d',
    require    => File["/etc/init.d/${name}"],
  }

}
