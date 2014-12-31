#!/usr/bin/perl -w

use strict;
use warnings;
use JSON::XS;
use Encode;
use Data::Dumper;
use LWP::Simple;



my %redir;
my @regexa=(
    sub{ $_[0]=~ s/ /_/},
    sub{ $_[0]=~ s/ //},
    sub{ $_[0]=~ s/\.//},
    sub{ $_[0]=~ s/ü/ue/},
    sub{ $_[0]=~ s/ä/ae/},
    sub{ $_[0]=~ s/ß/sz/},
    sub{ $_[0]=~ s/ß/ss/},
    );

sub processOAPI {
    my $url=shift;
    my $was=shift;

    my $text = get($url) or die;

    my $json=JSON::XS->new->latin1->decode ($text);


    foreach my $ele (@{$json->{"elements"}}) {
    
        if ($ele->{"type"} eq "relation") {
            my $name=$ele->{"tags"}->{"name"};
            my $durl="http://www.openstreetmap.org/relation/".$ele->{"id"};
            if (!(defined($redir{$name}))) {
                $redir{$name}=$durl;
            }
            if (!(defined($redir{$was."/".$name}))) {
                $redir{$was."/".$name}=$durl;
            }
        }
    }
}



my $bezirk_overpass='%2F*%0AThis%20has%20been%20generated%20by%20the%20overpass-turbo%20wizard.%0AThe%20original%20search%20was%3A%0A%E2%80%9Cadmin_level%3D9%20in%20Hamburg%E2%80%9D%0A*%2F%0A%5Bout%3Ajson%5D%5Btimeout%3A25%5D%3B%0A%2F%2F%20fetch%20area%20%E2%80%9CHamburg%E2%80%9D%20to%20search%20in%0Aarea%283602618040%29-%3E.searchArea%3B%0A%2F%2F%20gather%20results%0A%28%0A%20%20%2F%2F%20query%20part%20for%3A%20%E2%80%9Cadmin_level%3D9%E2%80%9D%0A%20%20node%5B%22admin_level%22%3D%229%22%5D%28area.searchArea%29%3B%0A%20%20way%5B%22admin_level%22%3D%229%22%5D%28area.searchArea%29%3B%0A%20%20relation%5B%22admin_level%22%3D%229%22%5D%28area.searchArea%29%3B%0A%29%3B%0A%2F%2F%20print%20results%0Aout%20body%3B%0A%3E%3B%0Aout%20skel%20qt%3B';

my $stadtteil_overpass='%5Bout%3Ajson%5D%5Btimeout%3A25%5D%3B%0A%2F%2F%20fetch%20area%20%E2%80%9CHamburg%E2%80%9D%20to%20search%20in%0Aarea%283602618040%29-%3E.searchArea%3B%0A%2F%2F%20gather%20results%0A%28%0A%20%20%2F%2F%20query%20part%20for%3A%20%E2%80%9Cadmin_level%3D10%E2%80%9D%0A%20%20relation%5B%22admin_level%22%3D%2210%22%5D%28area.searchArea%29%3B%0A%29%3B%0A%2F%2F%20print%20results%0Aout%20body%3B%0A%3E%3B%0Aout%20skel%20qt%3B';


my $url;
$url='http://overpass-api.de/api/interpreter?data='.$bezirk_overpass;
&processOAPI($url,"Bezirk");
$url='http://overpass-api.de/api/interpreter?data='.$stadtteil_overpass;
&processOAPI($url,"Stadtteil");

$redir{"doku"}="http://wiki.openstreetmap.org/wiki/Openstreetmap.hamburg";
$redir{"about"}="http://wiki.openstreetmap.org/wiki/Openstreetmap.hamburg";
$redir{"über"}="http://wiki.openstreetmap.org/wiki/Openstreetmap.hamburg";

foreach my $reg (@regexa) {
    foreach my $key (keys %redir) {
        my $oldkey=$key;
        $reg->($key);
        if (!defined($redir{$key})) {
            $redir{$key}=$redir{$oldkey};
        }
    }
}

print "server {\n\tlisten [2a01:4f8:a0:83a2::13]:80;\n\tlisten 213.239.213.148:80;\n";



foreach my $key (sort keys %redir) {
        
        print "\tlocation ~* \"^/$key\" {\n";
        print "\t\treturn 301 $redir{$key};\n";
        print "\t}\n\n";

}
print "\tlocation / {\n";
print "\t\treturn 301 http://openstreetmap.de/karte.html?lat=53.56&lon=10.07&zoom=11;\n";
print "\t}\n";

print "}\n";
