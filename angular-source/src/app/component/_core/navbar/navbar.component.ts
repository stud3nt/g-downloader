import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import { AuthService } from "../../../service/auth.service";
import { User } from "../../../model/user";
import { CookieService } from "ngx-cookie-service";

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html'
})
export class NavbarComponent implements OnInit {

	@Input() user: User = null;

	@Output() onLogout = new EventEmitter<boolean>();

	constructor(
		public auth: AuthService,
		protected cookie: CookieService
	) { }

	ngOnInit(): void {}

	public logout(): void {
		this.auth.logout().subscribe((response) => {
			this.onLogout.emit(true);
		}, (error) => {

		})
	}

	toggleNavbar() {
		return true;
	}

}
