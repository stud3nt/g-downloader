import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { AuthService } from "./service/auth.service";
import { JsonResponse } from "./model/json-response";
import { User } from "./model/user";
import { CookieService } from "ngx-cookie-service";
import { Router } from "@angular/router";

@Component({
	selector: 'app-root',
	templateUrl: './app.component.html',
	encapsulation: ViewEncapsulation.None
})

export class IndexComponent implements OnInit{

	public authenticated: number = 0;

	public bodyClass = 'hold-transition';

	public user: User = null;

	constructor(
		protected authService: AuthService,
		protected cookie: CookieService,
		protected router: Router,
	) {}

	ngOnInit(): void {
		this.checkAuthentication();
	}

	public checkAuthentication(): void {
		this.determineBodyClasses();
		this.authService.checkStatus().subscribe((response: JsonResponse) => {
			if (response.success()) { // user is logged in - creating objects and cookie;
				let user = new User(response.data);

				this.authenticated = 1;
				this.user = user;
				this.authService.user = user;
				this.authService.isLoggedIn = true;

				if (!this.cookie.get('X-CSRF-TOKEN')) {
					this.cookie.set('X-CSRF-TOKEN', user.token, 0);
					this.router.navigate(['/']);
				}
			} else { // used is logged out - deleting objects and clearing cookie;
				this.authenticated = -1;
				this.user = null;

				this.authService.user = null;
				this.authService.isLoggedIn = true;

				this.cookie.delete('X-CSRF-TOKEN');
				this.router.navigate(['/']);
			}
		});
	}

	private determineBodyClasses(): void {
		this.bodyClass =  'hold-transition sidebar-mini' + (
			(this.authenticated)
				? ' skin-black-light'
				: ' login-page'
		);
	}
}
