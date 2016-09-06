package resize;
use nginx;
use File::Path qw(mkpath);
use File::stat;
use Image::Magick;
use HTTP::Date;

#url: /resize/<type>/<level0>/<level1>/<level2>/<id>/<width>x|c<height>/<file>

our $base_dir = "/var/www/domus/web/uploads";
our $image;
 
sub image {
  my $r = shift;

  if ($r->request_method ne "GET") {
    return 405;
  }

  # is request valid?
  return 404 unless $r->uri =~ /^\/\w+\/[0-9a-z]+\/[0-9a-z]+\/[0-9a-z]+\/\d+\/\d+(x|c)\d+\/\w+\.(jpg|jpeg|gif|png)$/i;

  my $nil, $type, $l0, $l1, $l2, $id, $dim, $file, $width, $height;

  ($nil, $type, $l0, $l1, $l2, $id, $dim, $file) = split("/", $r->uri);
  ($width, $height) = split(/x|c/, $dim);

  # is size valid?
  return 400 unless $width > 20 && $width < 1200;
  return 400 unless $height > 20 && $height < 1200;
  

  my $source, $thumb, $thumb_path, $x;

  # do some ugly transformations
  $source = join("/", ($base_dir, $type, $l0, $l1, $l2, $id, "source", $file));
  $thumb_path = join("/", ($base_dir, $type, $l0, $l1, $l2, $id, $dim));
  $thumb = "${thumb_path}/${file}";

  return 404 unless -f $source;

  if (-f $thumb) {
    my $stat_source = stat($source);
    my $stat_thumb = stat($thumb);
    my $if_mod = $r->header_in('If-Modified-Since');

    if ($if_mod && str2time($if_mod) >= $stat_source->mtime) {
      return 304;
    }

    if ($stat_source->mtime <= $stat_thumb->mtime) {
      $r->header_out('Last-Modified', time2str($stat_thumb->mtime));
      $r->send_http_header;
      $r->sendfile($thumb);
      return OK;
    }
  }


  $image = new Image::Magick;
  $x = $image->Read($source);
  return 500 if $x;

  if ($dim =~ /\d+x\d+/) {
    $image->Scale($dim . ">");
  }
  elsif ($dim =~ /\d+c\d+/) {
    $image->Resize("${width}x${height}^");
    $image->Crop(geometry => "${width}x${height}", gravity => "Center");
  }

  $image->Normalize();
  $image->Trim();

  mkpath($thumb_path) unless -d $thumb_path;
  $x = $image->Write($thumb);
  return 500 if $x;

  $r->header_out('Last-Modified', time2str(time()));
  $r->send_http_header;
  $r->sendfile($thumb);
  return OK;
}
 
1;
__END__
