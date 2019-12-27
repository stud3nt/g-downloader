import {Component, Input, OnInit} from '@angular/core';
import { AuthService } from "../../../service/auth.service";
import { ConfigService } from "../../../service/config.service";
import { User } from "../../../model/user";
import { MenuNode } from "../../../model/menu-node";
import { NavigationEnd, Router } from "@angular/router";
import { RouterService } from "../../../service/router.service";

@Component({
  selector: 'app-menu',
  templateUrl: './menu.component.html'
})

export class MenuComponent implements OnInit {

	@Input() user: User = null;

	public menu: MenuNode[];

	constructor(
		public router: Router,
		protected routerService: RouterService,
		protected config: ConfigService
	) {
		// initialize menu after every route change
		this.router.events.forEach(
			(event) => {
				if (event instanceof NavigationEnd) {
					let component = this;

					setTimeout(function() {
						component.initializeMenu();
					}, 100);
				}
			}
		)
	}

	ngOnInit() {
		this.initializeMenu();
	}

	/**
	 * Initialize and configure menu based on current URL;
	 */
	public initializeMenu(): void {
		let menu = this.config.menu;
		let currentUrl = this.router.url;

		for (let menuIndex in menu) {
			let menuObj = menu[menuIndex];

			if (menuObj.childs.length > 0) { // if menu element has childs
				let found = false;

				for (let childIndex in menuObj.childs) {
					let submenuObj = menuObj.childs[childIndex];
					let submenuUrl = this.routerService.generateUrl(submenuObj.route, submenuObj.routeParams);

					menu[menuIndex].childs[childIndex].active = (submenuUrl === currentUrl); // select current element

					if (submenuUrl === currentUrl)
						found = true;
				}

				menu[menuIndex].open = found; // open tree if element is selected;
			} else { // if single menu element;
				let menuUrl = this.routerService.generateUrl(menuObj.route, menuObj.routeParams);
				let found = (menuUrl === currentUrl);

				menu[menuIndex].open = found;
				menu[menuIndex].active = found;
			}
		}

		this.menu = menu;
	}

}
