import { Component, OnInit } from '@angular/core';
import { AuthService } from "../../../service/auth.service";
import { ConfigService } from "../../../service/config.service";
import { User } from "../../../model/user";
import { MenuNode } from "../../../model/menu-node";
import { RouterService } from "../../../service/router.service";

@Component({
  selector: 'app-menu',
  templateUrl: './menu.component.html'
})

export class MenuComponent implements OnInit {

	user: User;
	menu: MenuNode[];

  constructor(public router: RouterService, protected auth: AuthService, protected config: ConfigService) {}

  ngOnInit() {
		this.user = this.auth.user;
		this.menu = this.config.menu;
	}

}
