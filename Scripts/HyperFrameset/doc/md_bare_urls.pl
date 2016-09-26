#!/usr/local/bin/perl
sub expand_bare_url { 
	my ($name, $url) = @_; 
	$name =~ s/-/ /g; 
	return "[$name]($url)"; 
} 

while (<>) { 
	s/\<((.*)\.md)\>/expand_bare_url($2, $1)/eg; 
	print; 
}

