import { Component, OnInit } from '@angular/core';
import { DownloadingStatus } from "../../../../enum/downloading-status";
import { DownloaderService } from "../../../../service/downloader.service";
import { JsonResponse } from "../../../../model/json-response";
import { RouterService } from "../../../../service/router.service";
import { FileType } from "../../../../enum/file-type";
import { WebSocketService } from "../../../../service/web-socket.service";
import { WebsocketOperation } from "../../../../enum/websocket-operation";
import { AuthService } from "../../../../service/auth.service";
import { ToastrDataService } from "../../../../service/data/toastr-data.service";
import { ParsedFile } from "../../../../model/parsed-file";

@Component({
	selector: 'app-download-mini-panel',
	templateUrl: './download-mini-panel.component.html'
})
export class DownloadMiniPanelComponent implements OnInit {

	public mainButtonClass: string = 'navbar-main-button inactive';
	public miniPanelClass: string = 'dropdown-menu';

	public dropdownVisible: boolean = false;
	public dropdownFilesQueue: ParsedFile[] = [];

	public DownloaderStatus = DownloadingStatus;
	public FileType = FileType;

	public _downloaderStatus: string = DownloadingStatus.Idle;

	private _websocketName: string = 'download_session';
	private _websocketDelay: number = 1500;

	constructor(
		private auth: AuthService,
		protected downloaderService: DownloaderService,
		protected toastrService: ToastrDataService,
		protected websocketService: WebSocketService,
		public routerService: RouterService
	) { }

	ngOnInit() {
		this.websocketService.createListener(
			this._websocketName, (response: JsonResponse) => {
				if (typeof response.data === 'object') {

					setTimeout(() => {
						this.sendStatusRequest();
					}, this._websocketDelay);
				}
			}, (error) => {
				this.toastrService.addError('PARSER ERROR', error.message);
				console.log(error);
			}, () => {
				console.log('COMPLETE');
			}
		);

		this.sendStatusRequest();
	}

	protected sendStatusRequest(): void {
		this.websocketService.sendRequest(
			this._websocketName,
			WebsocketOperation.DownloadListStatus,
			this.auth.user ? this.auth.user.apiToken : null
		);
	}

	/**
	 * Starts downloading files process
	 */
	public start(): void {
		switch (this._downloaderStatus) {
			case DownloadingStatus.Downloading:
				this._websocketDelay = 1500;
				return;

			case DownloadingStatus.Breaking:
			case DownloadingStatus.WaitingForResponse:
				this._downloaderStatus = DownloadingStatus.Idle;
				this._websocketDelay = 1500;
				return;

			case DownloadingStatus.Idle:
			case DownloadingStatus.Continuation:
				this._downloaderStatus = DownloadingStatus.Downloading;
				this._websocketDelay = 250;
		}
	}

	/**
	 * Stops downloading files process
	 */
	public stop(): void {
		this._downloaderStatus = DownloadingStatus.Breaking;
	}

	public toggleMiniPanel(): void {
		this.dropdownVisible = !this.dropdownVisible;
		this.determineClasses();
	}

	protected determineClasses(): void {
		this.miniPanelClass = 'dropdown-menu' + ((this.dropdownVisible) ? ' visible' : ' hidden');
	}

}
